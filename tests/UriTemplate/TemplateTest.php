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

namespace LeagueTest\Uri\UriTemplate;

use League\Uri\Exceptions\SyntaxError;
use League\Uri\UriTemplate\Template;
use League\Uri\UriTemplate\VariableBag;
use PHPUnit\Framework\TestCase;
use function var_export;

/**
 * @coversDefaultClass \League\Uri\UriTemplate\Template
 */
final class TemplateTest extends TestCase
{
    /**
     * @covers ::fromString
     * @covers ::__construct
     * @covers ::toString
     *
     * @dataProvider providesValidNotation
     */
    public function testItCanBeInstantiatedWithAValidNotation(string $notation): void
    {
        $template = Template::fromString($notation);
        self::assertSame($notation, $template->toString());
    }

    public function providesValidNotation(): iterable
    {
        return [
            'complex template' => ['http://example.com{+path}{/segments}{?query,more*,foo[]*}'],
            'template without expression' => ['foobar'],

        ];
    }

    /**
     * @covers ::fromString
     *
     * @dataProvider providesInvalidNotation
     */
    public function testItFailsToInstantiatedWithAnInvalidNotation(string $notation): void
    {
        self::expectException(SyntaxError::class);

        Template::fromString($notation);
    }

    public function providesInvalidNotation(): iterable
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
     * @covers ::__set_state
     */
    public function testSetState(): void
    {
        $notation = '{foo}{bar}';

        $template = Template::fromString($notation);

        self::assertEquals($template, eval('return '.var_export($template, true).';'));
    }

    /**
     * @covers ::variableNames
     *
     * @dataProvider expectedVariableNames
     */
    public function testGetVariableNames(string $template, array $expected): void
    {
        self::assertSame($expected, Template::fromString($template)->variableNames());
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
     * @covers ::fromString
     */
    public function testExpandAcceptsOnlyStringAndStringableObject(): void
    {
        self::expectException(\TypeError::class);

        Template::fromString(1);
    }

    /**
     * @dataProvider providesExpansion
     */
    public function testItCanExpandVariables(string $notation, array $variables, string $expected): void
    {
        self::assertSame($expected, Template::fromString($notation)->expand(new VariableBag($variables)));
    }

    public function providesExpansion(): iterable
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
