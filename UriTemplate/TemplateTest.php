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

use function file_get_contents;
use function is_array;
use function json_decode;
use function ltrim;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ExtractionResult::class)]
#[CoversClass(ExtractedValue::class)]
#[CoversClass(Expression::class)]
#[CoversClass(Literal::class)]
#[CoversClass(Operator::class)]
#[CoversClass(Template::class)]
#[CoversClass(TemplateCanNotBeExpanded::class)]
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

    public function test_expand_or_fail_if_at_least_one_variable_is_missing(): void
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
        string $template,
        string $value,
        array $expected,
    ): void {
        self::assertSame($expected, Template::new($template)->extract($value)->variables());
    }

    /**
     * @return iterable<non-empty-string, array{
     *     template: string,
     *     value: string,
     *     expected: array<string, string|array<string|null>|null>
     * }>
     */
    public static function provideExtractCases(): iterable
    {
        yield 'literal and expression' => [
            'template' => '/users/{id}',
            'value' => '/users/42',
            'expected' => ['id' => '42'],
        ];

        yield 'expression between literals' => [
            'template' => '/users/{id}/profile',
            'value' => '/users/42/profile',
            'expected' => ['id' => '42'],
        ];

        yield 'multiple expressions' => [
            'template' => '/users/{id}/posts/{post}',
            'value' => '/users/42/posts/123',
            'expected' => [
                'id' => '42',
                'post' => '123',
            ],
        ];

        yield 'same variable repeated with same value' => [
            'template' => '/{id}/{id}',
            'value' => '/42/42',
            'expected' => ['id' => '42'],
        ];

        yield 'same variable repeated with different value' => [
            'template' => '/{id}/{id}',
            'value' => '/42/43',
            'expected' => [],
        ];

        yield 'expression value contains following literal' => [
            'template' => '/{value}/end',
            'value' => '/foo/end/bar/end',
            'expected' => [
                'value' => 'foo/end/bar',
            ],
        ];

        yield 'adjacent expressions cannot be extracted' => [
            'template' => '/{foo}{bar}',
            'value' => '/onetwo',
            'expected' => [],
        ];

        yield 'expression with prefix modifier' => [
            'template' => '/{id:3}/profile',
            'value' => '/123/profile',
            'expected' => ['id' => '123'],
        ];

        yield 'exploded expression followed by literal' => [
            'template' => '{/tags*}/end',
            'value' => '/one/two/three/end',
            'expected' => ['tags' => ['one', 'two', 'three']],
        ];

        yield 'query variables' => [
            'template' => '{?foo,bar}',
            'value' => '?foo=one&bar=two',
            'expected' => [
                'foo' => 'one',
                'bar' => 'two',
            ],
        ];

        yield 'path parameter variable' => [
            'template' => '{;foo}',
            'value' => ';foo=one',
            'expected' => [
                'foo' => 'one',
            ],
        ];

        yield 'fragment expression' => [
            'template' => '{#fragment}',
            'value' => '#section',
            'expected' => [
                'fragment' => 'section',
            ],
        ];

        yield 'label expression' => [
            'template' => '{.name}',
            'value' => '.john',
            'expected' => [
                'name' => 'john',
            ],
        ];

        yield 'prefix modifier' => [
            'template' => '/hotels/{hotel:4}/bookings/{booking}',
            'value' => '/hotels/Ritz/bookings/42',
            'expected' => [
                'hotel' => 'Ritz',
                'booking' => '42',
            ],
        ];

        yield 'prefix modifier with longer value' => [
            'template' => '/hotels/{hotel:4}/bookings/{booking}',
            'value' => '/hotels/Ritz-Carlton/bookings/42',
            'expected' => [],
        ];

        yield 'prefix modifier counts decoded characters' => [
            'template' => '/hotels/{hotel:4}/bookings/{booking}',
            'value' => '/hotels/Rest%20%26%20Relax/bookings/42',
            'expected' => [],
        ];

        yield 'resolves ambiguous expression' => [
            'template' => 'https://{host}{/segments*}/{file}{.extensions*}',
            'value' => 'https://www.host.com/path/to/a/file.x.y',
            'expected' => [
                'host' => 'www.host.com',
                'segments' => ['path', 'to', 'a'],
                'file' => 'file',
                'extensions' => ['x', 'y'],
            ],
        ];

        yield 'resolves an exploded expression followed by its prefix delimiter' => [
            'template' => '{/segments*}/{file}',
            'value' => '/path/to/file',
            'expected' => [
                'segments' => ['path', 'to'],
                'file' => 'file',
            ],
        ];

        yield 'resolves an expression followed by a prefixed expression' => [
            'template' => '/{file}{.extensions*}',
            'value' => '/file.tar.gz',
            'expected' => [
                'file' => 'file',
                'extensions' => ['tar', 'gz'],
            ],
        ];

        yield 'backtracks when a valid extraction prevents the remaining template from matching' => [
            'template' => '{/segments*}/{file}',
            'value' => '/path/to/file',
            'expected' => [
                'segments' => ['path', 'to'],
                'file' => 'file',
            ],
        ];

        yield 'does not consume a fragment after a query expression' => [
            'template' => '/{term:1}/{term}{?a,b}',
            'value' => '/t/thomas?a=0&b=1#fragment',
            'expected' => [],
        ];

        yield 'does not consume a query or fragment after a path expression' => [
            'template' => '/{segments*}',
            'value' => '/path/to/file?foo=bar#fragment',
            'expected' => [],
        ];

        yield 'extracts a query expression up to the fragment boundary' => [
            'template' => '/{term:1}/{term}{?a,b}',
            'value' => '/t/thomas?a=0&b=1',
            'expected' => [
                'term' => 'thomas',
                'a' => '0',
                'b' => '1',
            ],
        ];

        yield 'matches a fragment explicitly following a query expression' => [
            'template' => '/{term:1}/{term}{?a,b}#fragment',
            'value' => '/t/thomas?a=0&b=1#fragment',
            'expected' => [
                'term' => 'thomas',
                'a' => '0',
                'b' => '1',
            ],
        ];

        yield 'does not consume a query after a path expression' => [
            'template' => '/{segments*}',
            'value' => '/path/to/file?foo=bar',
            'expected' => [],
        ];

        yield 'does not consume a fragment after a path expression' => [
            'template' => '/{segments*}',
            'value' => '/path/to/file#fragment',
            'expected' => [],
        ];

        yield 'matches a fragment expression following a query expression' => [
            'template' => '/{term:1}/{term}{?a,b}{#fragment}',
            'value' => '/t/thomas?a=0&b=1#section',
            'expected' => [
                'term' => 'thomas',
                'a' => '0',
                'b' => '1',
                'fragment' => 'section',
            ],
        ];

        yield 'does not consume a fragment after an exploded query expression' => [
            'template' => '{?foo*}',
            'value' => '?foo=a&foo=b#fragment',
            'expected' => [],
        ];

        yield 'extracts an empty fragment' => [
            'template' => '{#fragment}',
            'value' => '#',
            'expected' => [
                'fragment' => '',
            ],
        ];

        yield 'extracts an empty query value' => [
            'template' => '{?foo}',
            'value' => '?foo=',
            'expected' => [
                'foo' => '',
            ],
        ];

        yield 'extracts a bare query variable as an empty value' => [
            'template' => '{?foo}',
            'value' => '?foo',
            'expected' => [
                'foo' => '',
            ],
        ];

        yield 'does not consume a fragment after a path expression followed by a literal' => [
            'template' => '/{segments*}/end',
            'value' => '/path/to/end#fragment',
            'expected' => [],
        ];

        yield 'does not consume a query after a path parameter expression' => [
            'template' => '/{;foo}',
            'value' => '/;foo=bar?baz=qux',
            'expected' => [],
        ];

        yield 'extracts a single query variable' => [
            'template' => '{?a}',
            'value' => '?a=1',
            'expected' => [
                'a' => '1',
            ],
        ];

        yield 'extracts query variables in declared order' => [
            'template' => '{?a,b}',
            'value' => '?a=1&b=2',
            'expected' => [
                'a' => '1',
                'b' => '2',
            ],
        ];

        yield 'extracts query variables regardless of their order' => [
            'template' => '{?a,b}',
            'value' => '?b=2&a=1',
            'expected' => [
                'a' => '1',
                'b' => '2',
            ],
        ];

        yield 'extracts a query variable when another is missing' => [
            'template' => '{?a,b}',
            'value' => '?b=2',
            'expected' => [
                'a' => null,
                'b' => '2',
            ],
        ];

        yield 'extracts an empty query' => [
            'template' => '{?a,b}',
            'value' => '?',
            'expected' => [
                'a' => null,
                'b' => null,
            ],
        ];

        yield 'extracts a single exploded query variable' => [
            'template' => '{?a*}',
            'value' => '?a=1',
            'expected' => [
                'a' => ['1'],
            ],
        ];

        yield 'extracts repeated values for an exploded query variable' => [
            'template' => '{?a*}',
            'value' => '?a=1&a=2',
            'expected' => [
                'a' => ['1', '2'],
            ],
        ];

        yield 'extracts exploded variables regardless of their order' => [
            'template' => '{?a*,b*}',
            'value' => '?b=0&a=1',
            'expected' => [
                'a' => ['1'],
                'b' => ['0'],
            ],
        ];

        yield 'extracts repeated exploded values regardless of their order' => [
            'template' => '{?a*,b*}',
            'value' => '?b=0&a=1&b=2',
            'expected' => [
                'a' => ['1'],
                'b' => ['0', '2'],
            ],
        ];

        yield 'extracts an empty exploded query value' => [
            'template' => '{?a*}',
            'value' => '?a=',
            'expected' => [
                'a' => [''],
            ],
        ];

        yield 'extracts a missing query variable from consecutive expressions' => [
            'template' => '/api/{version}/users/{id}{?fields}',
            'value' => '/api/v1/users/12345',
            'expected' => [
                'version' => 'v1',
                'id' => '12345',
                'fields' => null,
            ],
        ];

        yield 'extracts a missing query variable following a literal' => [
            'template' => '/api/{version}/users/{id}/{?fields}',
            'value' => '/api/v1/users/12345/',
            'expected' => [
                'version' => 'v1',
                'id' => '12345',
                'fields' => null,
            ],
        ];

        yield 'extracts consecutive path expressions' => [
            'template' => '{/foo}{/bar}',
            'value' => '/one/two',
            'expected' => [
                'foo' => 'one',
                'bar' => 'two',
            ],
        ];

        yield 'extracts the same variable from different expression types (1)' => [
            'template' => '{count*}|{/count*}|{?count*}|{&count*}',
            'value' => 'one,two,three%7C/one/two/three%7C?count=one&count=two&count=three%7C&count=one&count=two&count=three',
            'expected' => [
                'count' => ['one', 'two', 'three'],
            ],
        ];

        yield 'extracts the same variable from different expression types (2)' => [
            'template' => '{count}|{/count}|{?count}|{&count}',
            'value' => 'one,two,three%7C/one,two,three%7C?count=one,two,three%7C&count=one,two,three',
            'expected' => [
                'count' => ['one', 'two', 'three'],
            ],
        ];

        yield 'extracts the same variable from different expression types (3)' => [
            'template' => '{count*}|{/count*}|{?count*}|{&count*}|{count}|{/count}|{?count}|{&count}',
            'value' => 'one,two,three%7C/one/two/three%7C?count=one&count=two&count=three%7C&count=one&count=two&count=three%7Cone,two,three%7C/one,two,three%7C?count=one,two,three%7C&count=one,two,three',
            'expected' => [
                'count' => ['one', 'two', 'three'],
            ],
        ];

        yield 'unable to reconcile the same variable when list order differs' => [
            'template' => '{count*}|{/count*}',
            'value' => 'one,three,two%7C/one/two/three',
            'expected' => [],
        ];

        yield 'decodes a percent-encoded query value' => [
            'template' => '{?value}',
            'value' => '?value=foo%20bar',
            'expected' => ['value' => 'foo bar'],
        ];

        yield 'decodes a percent-encoded query value containing a query delimiter' => [
            'template' => '{?value}',
            'value' => '?value=foo%26bar',
            'expected' => ['value' => 'foo&bar'],
        ];

        yield 'decodes a percent-encoded query value containing an equals sign' => [
            'template' => '{?value}',
            'value' => '?value=foo%3Dbar',
            'expected' => ['value' => 'foo=bar'],
        ];

        yield 'decodes a percent-encoded query value containing a fragment delimiter' => [
            'template' => '{?value}',
            'value' => '?value=foo%23bar',
            'expected' => ['value' => 'foo#bar'],
        ];

        yield 'decodes a percent-encoded path value' => [
            'template' => '/{value}',
            'value' => '/foo%20bar',
            'expected' => ['value' => 'foo bar'],
        ];

        yield 'decodes a percent-encoded path value containing a slash' => [
            'template' => '/{value}',
            'value' => '/foo%2Fbar',
            'expected' => ['value' => 'foo/bar'],
        ];

        yield 'does not treat an encoded slash as a path delimiter' => [
            'template' => '/{first}/{second}',
            'value' => '/foo%2Fbar/baz',
            'expected' => [
                'first' => 'foo/bar',
                'second' => 'baz',
            ],
        ];

        yield 'decodes a percent-encoded label value' => [
            'template' => '{.value}',
            'value' => '.foo%20bar',
            'expected' => ['value' => 'foo bar'],
        ];

        yield 'decodes a percent-encoded path parameter value' => [
            'template' => '{;value}',
            'value' => ';value=foo%20bar',
            'expected' => ['value' => 'foo bar'],
        ];

        yield 'decodes a percent-encoded fragment value' => [
            'template' => '{#value}',
            'value' => '#foo%20bar',
            'expected' => ['value' => 'foo bar'],
        ];

        yield 'decodes a percent-encoded fragment delimiter' => [
            'template' => '{#value}',
            'value' => '#foo%23bar',
            'expected' => ['value' => 'foo#bar'],
        ];

        yield 'decodes percent-encoded reserved characters in a simple expression' => [
            'template' => '{value}',
            'value' => 'foo%3Fbar%26baz',
            'expected' => ['value' => 'foo?bar&baz'],
        ];

        yield 'decodes multiple percent-encoded characters' => [
            'template' => '/{value}',
            'value' => '/Rest%20%26%20Relax',
            'expected' => ['value' => 'Rest & Relax'],
        ];

        yield 'decodes percent-encoded values in exploded path variables' => [
            'template' => '{/values*}',
            'value' => '/foo%20bar/baz%2Fqux',
            'expected' => [
                'values' => ['foo bar', 'baz/qux'],
            ],
        ];

        yield 'decodes percent-encoded values in exploded query variables' => [
            'template' => '{?values*}',
            'value' => '?values=foo%20bar&values=baz%26qux',
            'expected' => [
                'values' => ['foo bar', 'baz&qux'],
            ],
        ];

        yield 'extracts reserved characters from a reserved expression' => [
            'template' => '{+value}',
            'value' => 'foo/bar?baz%23qux',
            'expected' => ['value' => 'foo/bar?baz#qux'],
        ];

        yield 'decodes percent-encoded characters from a reserved expression' => [
            'template' => '{+value}',
            'value' => 'foo%20bar/baz',
            'expected' => ['value' => 'foo bar/baz'],
        ];

        yield 'extracts a reserved expression before a literal' => [
            'template' => '{+value}/end',
            'value' => 'foo/bar?baz/end',
            'expected' => ['value' => 'foo/bar?baz'],
        ];

        yield 'reconciles an exploded variable with a scalar variable' => [
            'template' => '{/id*}{?id}',
            'value' => '/person?id=person',
            'expected' => ['id' => 'person'],
        ];

        yield 'extracts exploded named values' => [
            'template' => '{;count*}',
            'value' => ';count=one;count=two;count=three',
            'expected' => ['count' => ['one', 'two', 'three']],
        ];

        yield 'extracts key-value pairs from a path parameter' => [
            'template' => '{;keys}',
            'value' => ';keys=semi,%3B,dot,.,comma,%2C',
            'expected' => ['keys' => ['semi' => ';', 'dot' => '.', 'comma' => ',']],
        ];
    }

    public function test_it_can_match_an_operator_prefixed_expression(): void
    {
        self::assertTrue(Template::new('{/tags*}/end')->match('/one/two/three/end'));
    }

    public function test_it_cannot_match_when_a_repeated_variable_differs(): void
    {
        self::assertFalse(Template::new('/{id}/{id}')->match('/42/43'));
    }

    public function test_extract_or_fail_throws_on_extraction_error(): void
    {
        $this->expectException(VariableCanNotBeExtracted::class);

        Template::new('/{id}/{id}')->extractOrFail('/42/43');
    }

    public function test_extract_or_fail_throws_on_missing_variable(): void
    {
        try {
            Template::new('/{foo}/{bar}/{?baz}')->extractOrFail('/42/43/');
            self::fail('Expected '.VariableCanNotBeExtracted::class.' to be thrown.');
        } catch (VariableCanNotBeExtracted $exception) {
            self::assertSame(['baz'], $exception->getMissingNames());
            self::assertContains(ExtractionErrorReason::MissingVariables, $exception->getReasons());
        }
    }

    public function test_extract_result_keeps_the_errors_on_failure(): void
    {
        $result = Template::new('/{foo}/{bar}/{?baz}')->extract('/42/43');

        self::assertCount(0, $result->missingNames());
        self::assertContains(ExtractionErrorReason::LiteralMismatch, $result->reasons());
        self::assertFalse($result->isSuccessful());
    }
}
