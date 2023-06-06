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
use League\Uri\UriTemplateSpecificationTestCase;

/**
 * @coversDefaultClass \League\Uri\UriTemplate\Template
 */
final class TemplateTest extends UriTemplateSpecificationTestCase
{
    protected static array $testFilenames = [
        'spec-examples.json',
        'negative-tests.json',
        'extended-tests.json',
    ];

    /**
     * @covers ::createFromString
     * @covers ::__construct
     *
     * @dataProvider providesValidNotation
     */
    public function testItCanBeInstantiatedWithAValidNotation(string $notation): void
    {
        $template = Template::createFromString($notation);
        self::assertSame($notation, $template->value);
    }

    public static function providesValidNotation(): iterable
    {
        return [
            'complex template' => ['http://example.com{+path}{/segments}{?query,more*,foo[]*}'],
            'template without expression' => ['foobar'],

        ];
    }

    /**
     * @covers ::createFromString
     *
     * @dataProvider providesInvalidNotation
     */
    public function testItFailsToInstantiatedWithAnInvalidNotation(string $notation): void
    {
        self::expectException(SyntaxError::class);

        Template::createFromString($notation);
    }

    public static function providesInvalidNotation(): iterable
    {
        return [
            ['fooba{r'],
            ['fooba}r'],
            ['fooba}{r'],
            ['{foo{bar'],
            ['{foo}}bar'],
        ];
    }

    /**
     * @dataProvider expectedVariableNames
     */
    public function testGetVariableNames(string $template, array $expected): void
    {
        self::assertSame($expected, Template::createFromString($template)->variableNames);
    }

    public static function expectedVariableNames(): iterable
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
     * @dataProvider providesExpansion
     */
    public function testItCanExpandVariables(string $notation, array $variables, string $expected): void
    {
        self::assertSame($expected, Template::createFromString($notation)->expand(new VariableBag($variables)));
    }

    public static function providesExpansion(): iterable
    {
        return [
            'with variables' => [
                'notation' => 'foobar{var}',
                'variables' => ['var' => 'yolo'],
                'expected' => 'foobaryolo',
            ],
            'with no variables' => [
                'notation' => 'foobar',
                'variables' => [],
                'expected' => 'foobar',
            ],
        ];
    }
}
