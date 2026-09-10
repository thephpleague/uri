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

#[CoversClass(Operator::class)]
#[CoversClass(ExtractedValue::class)]
#[CoversClass(ExtractionResult::class)]
final class OperatorTest extends TestCase
{
    /**
     * @param array<string, string|array<string>> $expected
     */
    #[DataProvider('provideExtractCases')]
    public function testExtract(Operator $operator, string $specifier, string $value, array $expected): void
    {
        self::assertSame($expected, $operator->extract(VarSpecifier::new($specifier), $value)->values());
    }

    /**
     * @return iterable<string, array{
     *     operator: Operator,
     *     specifier: string,
     *     value: string,
     *     expected: array<string, string|array<string>>
     * }>
     */
    public static function provideExtractCases(): iterable
    {
        yield 'simple value' => [
            'operator' => Operator::None,
            'specifier' => 'foo',
            'value' => 'bar',
            'expected' => ['foo' => 'bar'],
        ];

        yield 'percent encoded value' => [
            'operator' => Operator::None,
            'specifier' => 'foo',
            'value' => 'hello%20world',
            'expected' => ['foo' => 'hello world'],
        ];

        yield 'reserved characters' => [
            'operator' => Operator::ReservedChars,
            'specifier' => 'foo',
            'value' => 'foo/bar%3Fbaz',
            'expected' => ['foo' => 'foo/bar?baz'],
        ];

        yield 'label value' => [
            'operator' => Operator::Label,
            'specifier' => 'foo',
            'value' => 'bar',
            'expected' => ['foo' => 'bar'],
        ];

        yield 'path value' => [
            'operator' => Operator::Path,
            'specifier' => 'foo',
            'value' => 'bar',
            'expected' => ['foo' => 'bar'],
        ];

        yield 'path parameter' => [
            'operator' => Operator::PathParam,
            'specifier' => 'foo',
            'value' => 'foo=bar',
            'expected' => ['foo' => 'bar'],
        ];

        yield 'path parameter without value' => [
            'operator' => Operator::PathParam,
            'specifier' => 'foo',
            'value' => 'foo',
            'expected' => ['foo' => ''],
        ];

        yield 'query parameter' => [
            'operator' => Operator::Query,
            'specifier' => 'foo',
            'value' => 'foo=bar',
            'expected' => ['foo' => 'bar'],
        ];

        yield 'query parameter without value' => [
            'operator' => Operator::Query,
            'specifier' => 'foo',
            'value' => 'foo',
            'expected' => ['foo' => ''],
        ];

        yield 'query pair' => [
            'operator' => Operator::QueryPair,
            'specifier' => 'foo',
            'value' => 'foo=bar',
            'expected' => ['foo' => 'bar'],
        ];

        yield 'fragment value' => [
            'operator' => Operator::Fragment,
            'specifier' => 'foo',
            'value' => 'foo/bar%3Fbaz',
            'expected' => ['foo' => 'foo/bar?baz'],
        ];

        yield 'query exploded list' => [
            'operator' => Operator::Query,
            'specifier' => 'foo*',
            'value' => 'foo=one&foo=two&foo=three',
            'expected' => [
                'foo' => ['one', 'two', 'three'],
            ],
        ];

        yield 'query exploded associative array' => [
            'operator' => Operator::Query,
            'specifier' => 'foo*',
            'value' => 'one=a&two=b',
            'expected' => [
                'foo' => [
                    'one' => 'a',
                    'two' => 'b',
                ],
            ],
        ];

        yield 'path exploded list' => [
            'operator' => Operator::Path,
            'specifier' => 'foo*',
            'value' => 'one/two/three',
            'expected' => [
                'foo' => ['one', 'two', 'three'],
            ],
        ];

        yield 'path exploded associative' => [
            'operator' => Operator::Path,
            'specifier' => 'foo*',
            'value' => 'one=/two=/three=',
            'expected' => [
                'foo' => [
                    'one' => '',
                    'two' => '',
                    'three' => '',
                ],
            ],
        ];

        yield 'path exploded encoded equals' => [
            'operator' => Operator::Path,
            'specifier' => 'foo*',
            'value' => 'one%3Dtwo/three',
            'expected' => [
                'foo' => ['one=two', 'three'],
            ],
        ];

        yield 'query exploded associative array with different names' => [
            'operator' => Operator::Query,
            'specifier' => 'foo*',
            'value' => 'bar=one&baz=two',
            'expected' => [
                'foo' => [
                    'bar' => 'one',
                    'baz' => 'two',
                ],
            ],
        ];
    }

    #[DataProvider('provideInvalidExtractCases')]
    public function testExtractThrows(
        Operator $operator,
        string $specifier,
        string $value,
    ): void {
        $this->expectException(SyntaxError::class);

        $operator->extract(VarSpecifier::new($specifier), $value);
    }

    /**
     * @return iterable<string, array{
     *     operator: Operator,
     *     specifier: string,
     *     value: string,
     * }>
     */
    public static function provideInvalidExtractCases(): iterable
    {
        yield 'query exploded mixed list and associative array' => [
            'operator' => Operator::Query,
            'specifier' => 'foo*',
            'value' => 'foo=bar&bar=baz',
        ];

        yield 'query exploded mixed list and associative array with repeated name' => [
            'operator' => Operator::Query,
            'specifier' => 'foo*',
            'value' => 'foo=bar&bar=baz&foo=qux',
        ];
    }
}
