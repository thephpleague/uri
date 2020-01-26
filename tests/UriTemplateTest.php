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

namespace LeagueTest\Uri;

use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use League\Uri\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * @covers League\Uri\UriTemplate
 */
final class UriTemplateTest extends TestCase
{
    public function testGetTemplate(): void
    {
        $template = 'http://example.com{+path}{/segments}{?query,more*,foo[]*}';
        $variables = [
            'path'     => '/foo/bar',
            'segments' => ['one', 'two'],
            'query'    => 'test',
            'more'     => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
        ];

        $uriTemplate = new UriTemplate($template, $variables);

        self::assertSame($template, $uriTemplate->getTemplate());
    }

    public function testGetDefaultVariables(): void
    {
        $template = 'http://example.com{+path}{/segments}{?query,more*,foo[]*}';
        $variables = [
            'path'     => '/foo/bar',
            'segments' => ['one', 'two'],
            'query'    => 'test',
            'more'     => ['fun', 'ice cream'],
            'foo[]' => ['fizz', 'buzz'],
        ];

        $uriTemplate = new UriTemplate($template, $variables);
        self::assertSame($variables, $uriTemplate->getDefaultVariables());

        $uriTemplateEmpty = new UriTemplate($template, []);
        self::assertSame([], $uriTemplateEmpty->getDefaultVariables());
    }

    public function testWithTemplate(): void
    {
        $template = '{foo}{bar}';
        $uriTemplate = new UriTemplate($template);
        $newTemplate = $uriTemplate->withTemplate('{bar}{baz}');
        $altTemplate = $uriTemplate->withTemplate($template);

        self::assertInstanceOf(UriTemplate::class, $newTemplate);
        self::assertInstanceOf(UriTemplate::class, $altTemplate);
        self::assertSame($altTemplate->getTemplate(), $uriTemplate->getTemplate());
        self::assertSame($altTemplate->getDefaultVariables(), $uriTemplate->getDefaultVariables());

        self::assertNotSame($newTemplate->getTemplate(), $uriTemplate->getTemplate());
    }

    public function testWithDefaultVariables(): void
    {
        $template = '{foo}{bar}';
        $variables = ['foo' => 'foo', 'bar' => 'bar'];
        $newVariables = ['foo' => 'bar', 'bar' => 'foo'];

        $uriTemplate = new UriTemplate($template, $variables);
        $newTemplate = $uriTemplate->withDefaultVariables($newVariables);
        $altTemplate = $uriTemplate->withDefaultVariables($variables);

        self::assertInstanceOf(UriTemplate::class, $newTemplate);
        self::assertInstanceOf(UriTemplate::class, $altTemplate);
        self::assertSame($altTemplate->getDefaultVariables(), $uriTemplate->getDefaultVariables());
        self::assertSame($altTemplate->getDefaultVariables(), $uriTemplate->getDefaultVariables());

        self::assertNotSame($newTemplate->getDefaultVariables(), $uriTemplate->getDefaultVariables());
    }

    /**
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

    public function testExpandAcceptsOnlyStringAndStringableObject(): void
    {
        self::expectException(\TypeError::class);

        new UriTemplate(1);
    }

    /**
     * @dataProvider templateProvider
     */
    public function testExpandsUriTemplates(string $template, string $expectedUriString, array $variables): void
    {
        self::assertSame($expectedUriString, (new UriTemplate($template))->expand($variables)->__toString());
    }

    public function templateProvider(): iterable
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

    public function testUnsupportedValueType(): void
    {
        self::expectException(\TypeError::class);

        (new UriTemplate('{foo}'))->expand(['foo' => new \stdClass()]);
    }

    /**
     * @covers \League\Uri\Exceptions\TemplateCanNotBeExpanded
     * @dataProvider provideInvalidTemplate
     */
    public function testInvalidUriTemplate(string $template): void
    {
        self::expectException(TemplateCanNotBeExpanded::class);

        new UriTemplate($template);
    }

    /**
     * @see https://github.com/uri-templates/uritemplate-test/blob/master/negative-tests.json
     */
    public function provideInvalidTemplate(): iterable
    {
        return [
            'missing variables and operator' => ['{}'],
            'missing ending braces' => ['{/id*'],
            'missing starting braces' => ['/id*}'],
            'mismatch in at least one expression (1)' => ['http://example.com/}/{+foo}'],
            'mismatch in at least one expression (2)' => ['http://example.com/{/{+foo}'],
            'multiple starting operators' => ['{/?id}'],
            'invalid prefix' => ['{var:prefix}'],
            'multiple operator modifiers (1)' => ['{hello:2*}'] ,
            'duplicate operator' => ['{??hello}'] ,
            'reserved operator !' => ['{!hello}'] ,
            'space inside variable name' => ['{with space}'],
            'leading space in variable name (1)' => ['{ leading_space}'],
            'trailing space in variable name' => ['{trailing_space }'],
            'reserved operator =' => ['{=path}'] ,
            'forbidden operator $' => ['{$var}'],
            'reserved operator |' => ['{|var*}'],
            'using an operator modifier as an operator' => ['{*keys?}'],
            'variable name contains a reserved character (1)' => ['{?empty=default,var}'],
            'variable name contains invalid character (1)' => ['{var}{-prefix|/-/|var}'],
            'variable name contains invalid prefix' => ['?q={searchTerms}&amp;c={example:color?}'],
            'variable name contains a reserved character (2)' => ['x{?empty|foo=none}'],
            'variable name contains a reserved character (3)' => ['/h{#hello+}'],
            'variable name contains a reserved character (4)' => ['/h#{hello+}'],
            'multiple operator modifiers (2)' => ['{;keys:1*}'],
            'variable name contains invalid character (2)' => ['?{-join|&|var,list}'],
            'variable name contains invalid character (3)' => ['/people/{~thing}'],
            'variable name contains invalid character (4)' => ['/{default-graph-uri}'],
            'variable name contains invalid character (5)' => ['/sparql{?query,default-graph-uri}'],
            'variable name contains invalid character (6)' => ['/sparql{?query){&default-graph-uri*}'],
            'leading space in variable name (2)' => ['/resolution{?x, y}'],
        ];
    }

    /**
     * @covers \League\Uri\Exceptions\TemplateCanNotBeExpanded
     * @dataProvider invalidModifierToApply
     */
    public function testExpandThrowsExceptionIfTheModifierCanNotBeApplied(string $template, array $variables): void
    {
        self::expectException(TemplateCanNotBeExpanded::class);

        (new UriTemplate($template))->expand($variables);
    }

    /**
     * Following negative tests with wrong variable can only be detected at runtime.
     *
     * @see https://github.com/uri-templates/uritemplate-test/blob/master/negative-tests.json
     */
    public function invalidModifierToApply(): iterable
    {
        return [
            'can not apply a modifier on a hash value (1)' => [
                'template' => '{keys:1}',
                'variables' => [
                    'keys' => [
                        'semi' => ';',
                        'dot' => '.',
                        'comma' => ',',
                    ],
                ],
            ],
            'can not apply a modifier on a hash value (2)' => [
                'template' => '{+keys:1}',
                'variables' => [
                    'keys' => [
                        'semi' => ';',
                        'dot' => '.',
                        'comma' => ',',
                    ],
                ],
            ],
        ];
    }
}