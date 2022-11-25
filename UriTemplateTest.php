<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Uri;

use League\Uri\Exceptions\SyntaxError;
use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Uri\UriTemplate
 */
final class UriTemplateTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getTemplate
     */
    public function testGetTemplate(): void
    {
        $template = 'https://example.com{+path}{/segments}{?query,more*,foo[]*}';
        $variables = [
            'path'     => '/foo/bar',
            'segments' => ['one', 'two'],
            'query'    => 'test',
            'more'     => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
        ];

        $uriTemplate = new UriTemplate($template, $variables);

        self::assertSame($template, $uriTemplate->getTemplate());
        self::assertSame($template, $uriTemplate->template->value);
    }

    /**
     * @covers ::__construct
     * @covers ::filterVariables
     * @covers ::getDefaultVariables
     */
    public function testGetDefaultVariables(): void
    {
        $template = 'https://example.com{+path}{/segments}{?query,more*,foo[]*}';
        $variables = [
            'path'     => '/foo/bar',
            'segments' => ['one', 'two', 3, true, 'false', false, null],
            'query'    => 'test',
            'more'     => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
            'nonexistent' => ['random'],
        ];

        $expectedVariables = [
            'path'     => '/foo/bar',
            'segments' => ['one', 'two', '3', '1', 'false', '0', ''],
            'query'    => 'test',
            'more'     => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
        ];

        $uriTemplate = new UriTemplate($template, $variables);
        self::assertSame($expectedVariables, $uriTemplate->getDefaultVariables());
        self::assertSame($expectedVariables, $uriTemplate->defaultVariables->all());
        self::assertFalse($uriTemplate->defaultVariables->isEmpty());

        $uriTemplateEmpty = new UriTemplate($template, []);
        self::assertSame([], $uriTemplateEmpty->getDefaultVariables());
        self::assertSame([], $uriTemplateEmpty->defaultVariables->all());
        self::assertTrue($uriTemplateEmpty->defaultVariables->isEmpty());
    }

    /**
     * @covers ::filterVariables
     * @covers ::withDefaultVariables
     */
    public function testWithDefaultVariables(): void
    {
        $template = '{foo}{bar}';
        $variables = ['foo' => 'foo', 'bar' => 'bar'];
        $newVariables = ['foo' => 'bar', 'bar' => 'foo'];
        $newAltVariables = ['foo' => 'foo', 'bar' => 'bar', 'filteredVariable' => 'random'];

        $uriTemplate = new UriTemplate($template, $variables);
        $newTemplate = $uriTemplate->withDefaultVariables($newVariables);
        $altTemplate = $uriTemplate->withDefaultVariables($variables);
        $newAltTemplate = $uriTemplate->withDefaultVariables($newAltVariables);

        self::assertSame($altTemplate->getDefaultVariables(), $uriTemplate->getDefaultVariables());
        self::assertSame($newAltTemplate->getDefaultVariables(), $uriTemplate->getDefaultVariables());
        self::assertNotSame($newTemplate->getDefaultVariables(), $uriTemplate->getDefaultVariables());
    }

    /**
     * @covers ::__set_state
     */
    public function testSetState(): void
    {
        $template = '{foo}{bar}';
        $variables = ['foo' => 'foo', 'bar' => 'bar'];

        $uriTemplate = new UriTemplate($template, $variables);

        self::assertEquals($uriTemplate, eval('return '.var_export($uriTemplate, true).';'));
    }

    /**
     * @covers ::getVariableNames
     *
     * @dataProvider expectedVariableNames
     */
    public function testGetVariableNames(string $template, array $expected): void
    {
        self::assertSame($expected, (new UriTemplate($template))->getVariableNames());
    }

    public function expectedVariableNames(): iterable
    {
        return [
            [
                'template' => '',
                'expected' => [],
            ],
            [
                'template' => '{foo}{bar}',
                'expected' => ['foo', 'bar'],
            ],
            [
                'template' => '{foo}{foo:2}{+foo}',
                'expected' => ['foo'],
            ],
            [
                'template' => '{bar}{foo}',
                'expected' => ['bar', 'foo'],
            ],
        ];
    }

    /**
     * @covers ::expand
     *
     * @dataProvider templateExpansionProvider
     */
    public function testExpandsUriTemplates(string $template, string $expectedUriString, array $variables): void
    {
        self::assertSame($expectedUriString, (new UriTemplate($template))->expand($variables)->__toString());
    }

    public function templateExpansionProvider(): iterable
    {
        $variables = [
            'var'   => 'value',
            'hello' => 'Hello World!',
            'empty' => '',
            'path'  => '/foo/bar',
            'x'     => '1024',
            'y'     => '768',
            'null'  => null,
            'list'  => ['red', 'green', 'blue'],
            'keys'  => [
                'semi'  => ';',
                'dot'   => '.',
                'comma' => ',',
            ],
            'empty_keys' => [],
            'bool' => true,
        ];

        $templateAndExpansionData = [
            'level 1' => [
                ['foo',                 'foo'],
                ['{var}',               'value'],
                ['{hello}',             'Hello%20World%21'],
                ['{bool}',              '1'],
            ],
            'level 2' => [
                ['{+var}',              'value'],
                ['{+hello}',            'Hello%20World!'],
                ['{+path}/here',        '/foo/bar/here'],
                ['here?ref={+path}',    'here?ref=/foo/bar'],
            ],
            'level 3' => [
                ['X{#var}',             'X#value'],
                ['X{#hello}',           'X#Hello%20World!'],
                ['map?{x,y}',           'map?1024,768'],
                ['{x,hello,y}',         '1024,Hello%20World%21,768'],
                ['{+x,hello,y}',        '1024,Hello%20World!,768'],
                ['{+path,x}/here',      '/foo/bar,1024/here'],
                ['{#x,hello,y}',        '#1024,Hello%20World!,768'],
                ['{#path,x}/here',      '#/foo/bar,1024/here'],
                ['X{.var}',             'X.value'],
                ['X{.x,y}',             'X.1024.768'],
                ['{/var}',              '/value'],
                ['{/var,x}/here',       '/value/1024/here'],
                ['{;x,y}',              ';x=1024;y=768'],
                ['{;x,y,empty}',        ';x=1024;y=768;empty'],
                ['{?x,y}',              '?x=1024&y=768'],
                ['{?x,y,empty}',        '?x=1024&y=768&empty='],
                ['{?x,y,undef}',        '?x=1024&y=768'],
                ['?fixed=yes{&x}',      '?fixed=yes&x=1024'],
                ['{&x,y,empty}',        '&x=1024&y=768&empty='],
            ],
            'level 4' => [
                ['{var:3}',             'val'],
                ['{var:30}',            'value'],
                ['{list}',              'red,green,blue'],
                ['{list*}',             'red,green,blue'],
                ['{keys}',              'semi,%3B,dot,.,comma,%2C'],
                ['{keys*}',             'semi=%3B,dot=.,comma=%2C'],
                ['{+path:6}/here',      '/foo/b/here'],
                ['{+list}',             'red,green,blue'],
                ['{+list*}',            'red,green,blue'],
                ['{+keys}',             'semi,;,dot,.,comma,,'],
                ['{+keys*}',            'semi=;,dot=.,comma=,'],
                ['{#path:6}/here',      '#/foo/b/here'],
                ['{#list}',             '#red,green,blue'],
                ['{#list*}',            '#red,green,blue'],
                ['{#keys}',             '#semi,;,dot,.,comma,,'],
                ['{#keys*}',            '#semi=;,dot=.,comma=,'],
                ['X{.var:3}',           'X.val'],
                ['X{.list}',            'X.red,green,blue'],
                ['X{.list*}',           'X.red.green.blue'],
                ['X{.keys}',            'X.semi,%3B,dot,.,comma,%2C'],
                ['X{.keys*}',           'X.semi=%3B.dot=..comma=%2C'],
                ['{/var:1,var}',        '/v/value'],
                ['{/list}',             '/red,green,blue'],
                ['{/list*}',            '/red/green/blue'],
                ['{/list*,path:4}',     '/red/green/blue/%2Ffoo'],
                ['{/keys}',             '/semi,%3B,dot,.,comma,%2C'],
                ['{/keys*}',            '/semi=%3B/dot=./comma=%2C'],
                ['{;hello:5}',          ';hello=Hello'],
                ['{;list}',             ';list=red,green,blue'],
                ['{;list*}',            ';list=red;list=green;list=blue'],
                ['{;keys}',             ';keys=semi,%3B,dot,.,comma,%2C'],
                ['{;keys*}',            ';semi=%3B;dot=.;comma=%2C'],
                ['{?var:3}',            '?var=val'],
                ['{?list}',             '?list=red,green,blue'],
                ['{?list*}',            '?list=red&list=green&list=blue'],
                ['{?keys}',             '?keys=semi,%3B,dot,.,comma,%2C'],
                ['{?keys*}',            '?semi=%3B&dot=.&comma=%2C'],
                ['{&var:3}',            '&var=val'],
                ['{&list}',             '&list=red,green,blue'],
                ['{&list*}',            '&list=red&list=green&list=blue'],
                ['{&keys}',             '&keys=semi,%3B,dot,.,comma,%2C'],
                ['{&keys*}',            '&semi=%3B&dot=.&comma=%2C'],
                ['{.null}',            ''],
                ['{.null,var}',        '.value'],
                ['X{.empty_keys*}',     'X'],
                ['X{.empty_keys}',      'X'],
            ],
            'extra' => [
                // Test that missing expansions are skipped
                ['test{&missing*}',     'test'],
                // Test that multiple expansions can be set
                ['http://{var}/{var:2}{?keys*}', 'http://value/va?semi=%3B&dot=.&comma=%2C'],
                // Test more complex query string stuff
                ['http://www.test.com{+path}{?var,keys*}', 'http://www.test.com/foo/bar?var=value&semi=%3B&dot=.&comma=%2C'],
            ],
        ];

        foreach ($templateAndExpansionData as $specification => $tests) {
            foreach ($tests as $offset => $test) {
                yield $specification.' test '.$offset => [
                    'template' => $test[0],
                    'expectedUriString' => $test[1],
                    'variables' => $variables,
                ];
            }
        }
    }

    public function testAllowsQueryValuePairsArrayExpansion(): void
    {
        $template = 'http://example.com{+path}{/segments}{?query,more*,foo[]*}';
        $variables = [
            'path'     => '/foo/bar',
            'segments' => ['one', 'two'],
            'query'    => 'test',
            'more'     => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
        ];

        self::assertSame(
            'http://example.com/foo/bar/one,two?query=test&more=fun&more=ice%20cream&foo%5B%5D=fizz&foo%5B%5D=buzz',
            (new UriTemplate($template))->expand($variables)->__toString()
        );
    }

    /**
     * @covers \League\Uri\Exceptions\TemplateCanNotBeExpanded
     * @covers \League\Uri\UriTemplate\Template
     */
    public function testDisallowNestedArrayExpansion(): void
    {
        $template = 'http://example.com{?query,data*,foo*}';
        $variables = [
            'query'    => 'test',
            'data'     => [
                'more' => ['fun', 'ice cream'],
            ],
            'foo' => [
                'baz' => [
                    'bar'  => 'fizz',
                    'test' => 'buzz',
                ],
                'bam' => 'boo',
            ],
        ];

        self::expectException(TemplateCanNotBeExpanded::class);

        (new UriTemplate($template))->expand($variables);
    }

    /**
     * @covers ::expand
     * @covers ::filterVariables
     * @covers \League\Uri\UriTemplate\Template
     */
    public function testExpandWithDefaultVariables(): void
    {
        $template = 'http://example.com{+path}{/segments}{?query,more*,foo[]*}';

        $defaultVariables = [
            'path' => '/foo/bar',
            'segments' => ['one', 'two'],
        ];

        $variables = [
            'query' => 'test',
            'more' => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
        ];

        self::assertSame(
            'http://example.com/foo/bar/one,two?query=test&more=fun&more=ice%20cream&foo%5B%5D=fizz&foo%5B%5D=buzz',
            (new UriTemplate($template, $defaultVariables))->expand($variables)->__toString()
        );
    }

    /**
     * @covers ::expand
     * @covers ::filterVariables
     * @covers \League\Uri\UriTemplate\Template
     */
    public function testExpandWithDefaultVariablesWithOverride(): void
    {
        $template = 'http://example.com{+path}{/segments}{?query,more*,foo[]*}';

        $defaultVariables = [
            'path'     => '/foo/bar',
            'segments' => ['one', 'two'],
        ];

        $variables = [
            'path' => '/bar/baz',
            'query'    => 'test',
            'more'     => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
        ];

        self::assertSame(
            'http://example.com/bar/baz/one,two?query=test&more=fun&more=ice%20cream&foo%5B%5D=fizz&foo%5B%5D=buzz',
            (new UriTemplate($template, $defaultVariables))->expand($variables)->__toString()
        );
    }

    /**
     * @covers \League\Uri\UriTemplate\Template
     *
     * @dataProvider provideInvalidTemplate
     */
    public function testInvalidUriTemplate(string $template): void
    {
        self::expectException(SyntaxError::class);

        new UriTemplate($template);
    }

    /**
     * @see https://github.com/uri-templates/uritemplate-test/blob/master/negative-tests.json
     */
    public function provideInvalidTemplate(): iterable
    {
        return [
            'mismatch in at least one expression (1)' => ['http://example.com/}/{+foo}'],
            'mismatch in at least one expression (2)' => ['http://example.com/{/{+foo}'],
        ];
    }

    /**
     * @covers \League\Uri\UriTemplate\Template
     */
    public function testExpansionWithMultipleSameExpression(): void
    {
        $template = '{foo}/{foo}';
        $data = ['foo' => 'foo'];

        self::assertSame('foo/foo', (new UriTemplate($template, $data))->expand()->__toString());
    }
}
