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

#[CoversClass(Literal::class)]
#[CoversClass(Template::class)]
#[CoversClass(ExtractionResult::class)]
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
                    yield $title.' - '.$level.' # '.($offset + 1).' ['.$input.']'  => [
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

    #[Test]
    public function it_can_expand_with_expand_or_fail_when_all_variables_are_present(): void
    {
        self::assertSame('barfoo', Template::new('{var}{baz}')->expandOrFail(['var' => 'bar', 'baz' => 'foo']));
    }

    #[Test]
    #[DataProvider('providesLiteralEncoding')]
    public function it_encodes_the_template_literals(string $notation, array $variables, string $expected): void
    {
        self::assertSame($expected, Template::new($notation)->expand($variables));
    }

    public function test_it_can_match_the_content(): void
    {
        self::assertTrue(Template::new('/users/{id}')->match('/users/42'));
        self::assertFalse(Template::new('/users/{id}')->match('/articles/42'));
    }

    public function test_it_extract_or_fail_will_throw_on_error(): void
    {
        $this->expectException(VariableCanNotBeExtracted::class);

        Template::new('/users/{id}')->extractOrFail('/articles/42');
    }

    /**
     * @see https://www.rfc-editor.org/rfc/rfc6570#section-3.1
     *
     * @return iterable<non-empty-string, array{
     *     notation: string,
     *     variables: array<non-empty-string>,
     *     expected: string,
     * }>
     */
    public static function providesLiteralEncoding(): iterable
    {
        $variables = [
            'var' => 'value',
            'path' => '/foo/bar',
            'hello' => 'Hello World!',
        ];

        return [
            'a character disallowed in a URI is encoded as its UTF-8 octets' => [
                'notation' => 'café/{var}',
                'variables' => $variables,
                'expected' => 'caf%C3%A9/value',
            ],
            'a percent encoded triplet is copied as is' => [
                'notation' => 'x%20y/{var}',
                'variables' => $variables,
                'expected' => 'x%20y/value',
            ],
            'a percent encoded triplet is copied as is on both sides of an expression' => [
                'notation' => 'x%20y{var}z%20w',
                'variables' => $variables,
                'expected' => 'x%20yvaluez%20w',
            ],
            'a space is encoded' => [
                'notation' => 'a b/{var}',
                'variables' => $variables,
                'expected' => 'a%20b/value',
            ],
            'a percent sign which starts no triplet is encoded' => [
                'notation' => '100%/{var}',
                'variables' => $variables,
                'expected' => '100%25/value',
            ],
            'reserved characters are copied as is' => [
                'notation' => "/a[0]:b@c!d\$e&f'g(h)i*j+k,l;m=n?o#{var}",
                'variables' => $variables,
                'expected' => "/a[0]:b@c!d\$e&f'g(h)i*j+k,l;m=n?o#value",
            ],
            'the expanded expressions are never encoded twice' => [
                'notation' => 'café{+path}{#hello}',
                'variables' => $variables,
                'expected' => 'caf%C3%A9/foo/bar#Hello%20World!',
            ],
        ];
    }

    /**
     * @param array<string, string|array<string>> $expected
     */
    #[DataProvider('provideExtractCases')]
    public function testExtract(
        Template $template,
        string $value,
        array $expected,
    ): void {
        self::assertSame($expected, $template->extract($value)->values());
    }

    /**
     * @return iterable<non-empty-string, array{
     *     template: Template,
     *     value: string,
     *     expected: array<string, string|array<string>>
     * }>
     */
    public static function provideExtractCases(): iterable
    {
        yield 'literal and expression' => [
            'template' => Template::new('/users/{id}'),
            'value' => '/users/42',
            'expected' => ['id' => '42'],
        ];

        yield 'expression between literals' => [
            'template' => Template::new('/users/{id}/profile'),
            'value' => '/users/42/profile',
            'expected' => ['id' => '42'],
        ];

        yield 'multiple expressions' => [
            'template' => Template::new('/users/{id}/posts/{post}'),
            'value' => '/users/42/posts/123',
            'expected' => [
                'id' => '42',
                'post' => '123',
            ],
        ];

        yield 'same variable repeated with same value' => [
            'template' => Template::new('/{id}/{id}'),
            'value' => '/42/42',
            'expected' => ['id' => '42'],
        ];

        yield 'same variable repeated with different value' => [
            'template' => Template::new('/{id}/{id}'),
            'value' => '/42/43',
            'expected' => [],
        ];

        yield 'expression value contains following literal' => [
            'template' => Template::new('/{value}/end'),
            'value' => '/foo/end/bar/end',
            'expected' => [
                'value' => 'foo/end/bar',
            ],
        ];

        yield 'adjacent expressions cannot be extracted' => [
            'template' => Template::new('/{foo}{bar}'),
            'value' => '/onetwo',
            'expected' => [],
        ];

        yield 'expression with prefix modifier' => [
            'template' => Template::new('/{id:3}/profile'),
            'value' => '/123/profile',
            'expected' => ['id' => '123'],
        ];

        yield 'exploded expression followed by literal' => [
            'template' => Template::new('{/tags*}/end'),
            'value' => '/one/two/three/end',
            'expected' => ['tags' => ['one', 'two', 'three']],
        ];

        yield 'path exploded variable' => [
            'template' => Template::new('{/tags*}/end'),
            'value' => '/one/two/three/end',
            'expected' => [
                'tags' => ['one', 'two', 'three'],
            ],
        ];

        yield 'query variables' => [
            'template' => Template::new('{?foo,bar}'),
            'value' => '?foo=one&bar=two',
            'expected' => [
                'foo' => 'one',
                'bar' => 'two',
            ],
        ];

        yield 'path parameter variable' => [
            'template' => Template::new('{;foo}'),
            'value' => ';foo=one',
            'expected' => [
                'foo' => 'one',
            ],
        ];

        yield 'repeated variable with same value' => [
            'template' => Template::new('/{id}/{id}'),
            'value' => '/42/42',
            'expected' => [
                'id' => '42',
            ],
        ];

        yield 'repeated variable with different values' => [
            'template' => Template::new('/{id}/{id}'),
            'value' => '/42/43',
            'expected' => [],
        ];

        yield 'expression between literals uses first literal occurrence' => [
            'template' => Template::new('/{value}/end'),
            'value' => '/foo/end/bar/end',
            'expected' => [
                'value' => 'foo/end/bar',
            ],
        ];

        yield 'adjacent expressions cannot be arbitrarily partitioned' => [
            'template' => Template::new('/{foo}{bar}'),
            'value' => '/onetwo',
            'expected' => [],
        ];

        yield 'fragment expression' => [
            'template' => Template::new('{#fragment}'),
            'value' => '#section',
            'expected' => [
                'fragment' => 'section',
            ],
        ];

        yield 'label expression' => [
            'template' => Template::new('{.name}'),
            'value' => '.john',
            'expected' => [
                'name' => 'john',
            ],
        ];

        yield 'semicolon expression' => [
            'template' => Template::new('{;foo}'),
            'value' => ';foo=one',
            'expected' => [
                'foo' => 'one',
            ],
        ];

        yield 'prefix modifier' => [
            'template' => Template::new('/hotels/{hotel:4}/bookings/{booking}'),
            'value' => '/hotels/Ritz/bookings/42',
            'expected' => [
                'hotel' => 'Ritz',
                'booking' => '42',
            ],
        ];

        yield 'prefix modifier with longer value' => [
            'template' => Template::new('/hotels/{hotel:4}/bookings/{booking}'),
            'value' => '/hotels/Ritz-Carlton/bookings/42',
            'expected' => [],
        ];

        yield 'prefix modifier counts decoded characters' => [
            'template' => Template::new('/hotels/{hotel:4}/bookings/{booking}'),
            'value' => '/hotels/Rest%20%26%20Relax/bookings/42',
            'expected' => [],
        ];

        yield 'resolves ambiguous expression' => [
            'template' => Template::new('https://{host}{/segments*}/{file}{.extensions*}'),
            'value' => 'https://www.host.com/path/to/a/file.x.y',
            'expected' => [
                'host' => 'www.host.com',
                'segments' => ['path', 'to', 'a'],
                'file' => 'file',
                'extensions' => ['x', 'y'],
            ],
        ];

        yield 'resolves an exploded expression followed by its prefix delimiter' => [
            'template' => Template::new('{/segments*}/{file}'),
            'value' => '/path/to/file',
            'expected' => [
                'segments' => ['path', 'to'],
                'file' => 'file',
            ],
        ];

        yield 'resolves an expression followed by a prefixed expression' => [
            'template' => Template::new('/{file}{.extensions*}'),
            'value' => '/file.tar.gz',
            'expected' => [
                'file' => 'file',
                'extensions' => ['tar', 'gz'],
            ],
        ];

        yield 'backtracks when a valid extraction prevents the remaining template from matching' => [
            'template' => Template::new('{/segments*}/{file}'),
            'value' => '/path/to/file',
            'expected' => [
                'segments' => ['path', 'to'],
                'file' => 'file',
            ],
        ];
    }

    public function test_it_can_match_an_operator_prefixed_expression(): void
    {
        self::assertTrue(
            Template::new('{/tags*}/end')->match('/one/two/three/end'),
        );
    }

    public function test_it_cannot_match_when_a_repeated_variable_differs(): void
    {
        self::assertFalse(
            Template::new('/{id}/{id}')->match('/42/43'),
        );
    }

    public function test_extract_or_fail_throws_when_a_repeated_variable_differs(): void
    {
        $this->expectException(VariableCanNotBeExtracted::class);

        Template::new('/{id}/{id}')->extractOrFail('/42/43');
    }
}
