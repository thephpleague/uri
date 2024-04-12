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

use JsonException;
use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Template::class)]
final class TemplateTest extends TestCase
{
    private static string $rootPath = __DIR__.'/../../vendor/uri-templates/uritemplate-test';

    /** @var array<string> */
    private static array $testFilenames = [
        'spec-examples.json',
        'negative-tests.json',
        'extended-tests.json',
    ];

    #[DataProvider('uriTemplateSpecificationDataProvider')]
    #[Test]
    public function testItCompliesWithUriTemplatesExpansionTests(
        array $variables,
        string $input,
        string|array|false $expected
    ): void {
        if (false === $expected) {
            $this->expectException(Throwable::class);
        }

        $result = Template::new($input)->expand(new VariableBag($variables));

        if (is_array($expected)) {
            self::assertContains($result, $expected);
        } else {
            self::assertSame($expected, $result);
        }
    }

    /**
     * @throws JsonException
     * @throws RuntimeException
     * @return iterable<string, array{
     *     variables:array{string, string|int},
     *     input:string,
     *     expected:string|array<string>|false
     * }>
     */
    public static function uriTemplateSpecificationDataProvider(): iterable
    {
        foreach (static::$testFilenames as $path) {
            $path = static::$rootPath.'/'.ltrim($path, '/');
            if (false === $content = file_get_contents($path)) {
                throw new RuntimeException("unable to connect to the path `$path`.");
            }

            /** @var array $records */
            $records = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            foreach ($records as $title => $testSuite) {
                $level = $testSuite['level'] ?? null;
                $variables = $testSuite['variables'];
                foreach ($testSuite['testcases'] as $offset => [$input, $expected]) {
                    yield $title.' - '.$level.' # '.($offset + 1) => [
                        'variables' => $variables,
                        'input' => $input,
                        'expected' => $expected,
                    ];
                }
            }
        }
    }

    #[DataProvider('providesValidNotation')]
    public function testItCanBeInstantiatedWithAValidNotation(string $notation): void
    {
        self::assertSame($notation, Template::new($notation)->value);
        self::assertSame($notation, (string) Template::new($notation));
    }

    public static function providesValidNotation(): iterable
    {
        return [
            'complex template' => ['http://example.com{+path}{/segments}{?query,more*,foo[]*}'],
            'template without expression' => ['foobar'],

        ];
    }

    #[DataProvider('providesInvalidNotation')]
    public function testItFailsToInstantiatedWithAnInvalidNotation(string $notation): void
    {
        self::expectException(SyntaxError::class);

        Template::new($notation);
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

    #[DataProvider('expectedVariableNames')]
    public function testGetVariableNames(string $template, array $expected): void
    {
        self::assertSame($expected, Template::new($template)->variableNames);
    }

    public static function expectedVariableNames(): iterable
    {
        return [
            [
                'template' => '',
                'expected' => [],
            ],
            [
                'template' => '{foo}{bar}{420}',
                'expected' => ['foo', 'bar', '420'],
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

    #[DataProvider('providesExpansion')]
    public function testItCanExpandVariables(string $notation, array $variables, string $expected): void
    {
        self::assertSame($expected, Template::new($notation)->expand($variables));
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

    public function testExpandOrFailIfAtLeastOneVariableIsMissing(): void
    {
        $this->expectException(TemplateCanNotBeExpanded::class);

        Template::new('{var}{baz}')->expandOrFail(['var' => 'bar']);
    }
}
