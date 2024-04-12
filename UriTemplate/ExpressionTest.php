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

namespace League\Uri\UriTemplate;

use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Expression::class)]
final class ExpressionTest extends TestCase
{
    #[DataProvider('providesValidNotation')]
    public function testItCanBeInstantiatedWithAValidNotation(string $notation, array $variableNames): void
    {
        $expression = Expression::new($notation);
        self::assertSame($notation, $expression->value);
        self::assertSame($variableNames, $expression->variableNames);
    }

    public static function providesValidNotation(): iterable
    {
        return [
            'level 1' => ['notation' => '{var}', 'variableNames' => ['var']],
            'level 2' => ['notation' => '{+var}', 'variableNames' => ['var']],
            'level 3.1' => ['notation' => '{#var}', 'variableNames' => ['var']],
            'level 3.2' => ['notation' => '{x,y}', 'variableNames' => ['x', 'y']],
            'level 3.3' => ['notation' => '{+x,hello,y}', 'variableNames' => ['x', 'hello', 'y']],
            'level 3.4' => ['notation' => '{#path,x}', 'variableNames' => ['path', 'x']],
            'level 3.5' => ['notation' => '{.x,y}', 'variableNames' => ['x', 'y']],
            'level 3.6' => ['notation' => '{/var,x}', 'variableNames' => ['var', 'x']],
            'level 3.7' => ['notation' => '{;x,y,empty}', 'variableNames' => ['x', 'y', 'empty']],
            'level 3.8' => ['notation' => '{?x,y,undef}', 'variableNames' => ['x', 'y', 'undef']],
            'level 3.9' => ['notation' => '{&x,y,empty}', 'variableNames' => ['x', 'y', 'empty']],
            'level 4.1' => ['notation' => '{+path:6}', 'variableNames' => ['path']],
            'level 4.2' => ['notation' => '{var:3}', 'variableNames' => ['var']],
            'level 4.3' => ['notation' => '{#keys*}', 'variableNames' => ['keys']],
            'level 4.4' => ['notation' => '{/var:1,var}', 'variableNames' => ['var']],
            'level 4.5' => ['notation' => '{;keys*}', 'variableNames' => ['keys']],
            'level 4.6' => ['notation' => '{?var:3}', 'variableNames' => ['var']],
            'level 4.7' => ['notation' => '{.null}', 'variableNames' => ['null']],
        ];
    }

    #[DataProvider('providesInvalidExpression')]
    public function testExpressionConstructFailsWithInvalidString(string $expression): void
    {
        self::expectException(SyntaxError::class);

        Expression::new($expression);
    }

    public static function providesInvalidExpression(): iterable
    {
        return [
            'missing content' => ['{}'],
            'missing delimiter' => ['foobar'],
            'reserved operator' => ['{|var*}'],
            'missing ending braces' => ['{/id*'],
            'missing starting braces' => ['/id*}'],
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
            'variable name contains invalid character (1)' => ['{-prefix|/-/|var}'],
            'variable name contains invalid prefix' => ['{example:color?}'],
            'variable name contains a reserved character (2)' => ['{?empty|foo=none}'],
            'variable name contains a reserved character (3)' => ['{#hello+}'],
            'variable name contains a reserved character (4)' => ['{hello+}'],
            'multiple operator modifiers (2)' => ['{;keys:1*}'],
            'variable name contains invalid character (2)' => ['{-join|&|var,list}'],
            'variable name contains invalid character (3)' => ['{~thing}'],
            'variable name contains invalid character (4)' => ['{default-graph-uri}'],
            'variable name contains invalid character (5)' => ['{?query,default-graph-uri}'],
            'variable name contains invalid character (6)' => ['{?query){&default-graph-uri*}'],
            'leading space in variable name (2)' => ['{?x, y}'],
        ];
    }

    #[DataProvider('templateExpansionProvider')]
    public function testExpandsUriTemplates(string $template, string $expectedUriString, array $variables): void
    {
        self::assertSame($expectedUriString, Expression::new($template)->expand(new VariableBag($variables)));
    }

    public static function templateExpansionProvider(): iterable
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
                ['{var}',          'value'],
                ['{hello}',        'Hello%20World%21'],
                ['{bool}',         '1'],
            ],
            'level 2' => [
                ['{+var}',         'value'],
                ['{+hello}',       'Hello%20World!'],
                ['{+path}',        '/foo/bar'],
            ],
            'level 3' => [
                ['{#var}',          '#value'],
                ['{#hello}',        '#Hello%20World!'],
                ['{x,y}',           '1024,768'],
                ['{x,hello,y}',     '1024,Hello%20World%21,768'],
                ['{+x,hello,y}',    '1024,Hello%20World!,768'],
                ['{+path,x}',       '/foo/bar,1024'],
                ['{#x,hello,y}',    '#1024,Hello%20World!,768'],
                ['{#path,x}',       '#/foo/bar,1024'],
                ['{.var}',          '.value'],
                ['{.x,y}',          '.1024.768'],
                ['{/var}',          '/value'],
                ['{/var,x}',        '/value/1024'],
                ['{;x,y}',          ';x=1024;y=768'],
                ['{;x,y,empty}',    ';x=1024;y=768;empty'],
                ['{?x,y}',          '?x=1024&y=768'],
                ['{?x,y,empty}',    '?x=1024&y=768&empty='],
                ['{?x,y,undef}',    '?x=1024&y=768'],
                ['{&x}',            '&x=1024'],
                ['{&x,y,empty}',    '&x=1024&y=768&empty='],
            ],
            'level 4' => [
                ['{var:3}',         'val'],
                ['{var:30}',        'value'],
                ['{list}',          'red,green,blue'],
                ['{list*}',         'red,green,blue'],
                ['{keys}',          'semi,%3B,dot,.,comma,%2C'],
                ['{keys*}',         'semi=%3B,dot=.,comma=%2C'],
                ['{+path:6}',       '/foo/b'],
                ['{+list}',         'red,green,blue'],
                ['{+list*}',        'red,green,blue'],
                ['{+keys}',         'semi,;,dot,.,comma,,'],
                ['{+keys*}',        'semi=;,dot=.,comma=,'],
                ['{#path:6}',       '#/foo/b'],
                ['{#list}',         '#red,green,blue'],
                ['{#list*}',        '#red,green,blue'],
                ['{#keys}',         '#semi,;,dot,.,comma,,'],
                ['{#keys*}',        '#semi=;,dot=.,comma=,'],
                ['{.var:3}',       '.val'],
                ['{.list}',        '.red,green,blue'],
                ['{.list*}',       '.red.green.blue'],
                ['{.keys}',        '.semi,%3B,dot,.,comma,%2C'],
                ['{.keys*}',       '.semi=%3B.dot=..comma=%2C'],
                ['{/var:1,var}',    '/v/value'],
                ['{/list}',         '/red,green,blue'],
                ['{/list*}',        '/red/green/blue'],
                ['{/list*,path:4}', '/red/green/blue/%2Ffoo'],
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
                ['{.empty_keys*}',     ''],
                ['{.empty_keys}',      ''],
            ],
            'extra' => [
                // Test that missing expansions are skipped
                ['{&missing*}', ''],
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

    #[DataProvider('invalidModifierToApply')]
    public function testExpandThrowsExceptionIfTheModifierCanNotBeApplied(string $expression, array $variables): void
    {
        self::expectException(TemplateCanNotBeExpanded::class);

        Expression::new($expression)->expand(new VariableBag($variables));
    }

    /**
     * Following negative tests with wrong variable can only be detected at runtime.
     *
     * @see https://github.com/uri-templates/uritemplate-test/blob/master/negative-tests.json
     */
    public static function invalidModifierToApply(): iterable
    {
        return [
            'cannot apply a modifier on a hash value (1)' => [
                'expression' => '{keys:1}',
                'variables' => [
                    'keys' => [
                        'semi' => ';',
                        'dot' => '.',
                        'comma' => ',',
                    ],
                ],
            ],
            'cannot apply a modifier on a hash value (2)' => [
                'expression' => '{+keys:1}',
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
