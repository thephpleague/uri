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

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtractedValue::class)]
final class ExtractedValueTest extends TestCase
{
    #[DataProvider('provideValues')]
    public function testFromValue(
        string $value,
        string $specifier,
        string|array $expectedValue,
        bool $expectedPartial,
    ): void {
        $extracted = ExtractedValue::fromValue($value, VarSpecifier::new($specifier), Operator::None);

        self::assertSame($expectedValue, $extracted->value);
        self::assertSame($expectedPartial, $extracted->isPartial);
    }

    public static function provideValues(): iterable
    {
        yield 'complete scalar' => [
            'john',
            'term',
            'john',
            false,
        ];

        yield 'partial scalar' => [
            'j',
            'term:1',
            'j',
            true,
        ];

        yield 'partial scalar with larger maximum' => [
            'john',
            'term:10',
            'john',
            true,
        ];
    }

    public function testNegativeMaxLengthOtherThanMinusOneIsRejected(): void
    {
        $this->expectException(VariableCanNotBeExtracted::class);

        ExtractedValue::fromString('john', -2);
    }

    #[DataProvider('provideEqualValues')]
    public function testEquals(
        ExtractedValue $value,
        mixed $other,
        bool $expected,
    ): void {
        self::assertSame($expected, $value->equals($other));
    }

    public static function provideEqualValues(): iterable
    {
        yield 'same complete value' => [
            ExtractedValue::fromString('john'),
            ExtractedValue::fromString('john'),
            true,
        ];

        yield 'same partial value' => [
            ExtractedValue::fromString('j', 1),
            ExtractedValue::fromString('j', 1),
            true,
        ];

        yield 'same value but different maximum length' => [
            ExtractedValue::fromString('john', 4),
            ExtractedValue::fromString('john', 5),
            false,
        ];

        yield 'different values' => [
            ExtractedValue::fromString('john'),
            ExtractedValue::fromString('jane'),
            false,
        ];

        yield 'different types' => [
            ExtractedValue::fromString('john'),
            ExtractedValue::fromArray(['john']),
            false,
        ];

        yield 'not an ExtractedValue' => [
            ExtractedValue::fromString('john'),
            'john',
            false,
        ];
    }

    #[DataProvider('provideReconciliations')]
    public function testReconcile(
        ExtractedValue $value,
        ExtractedValue $other,
        ?ExtractedValue $expected,
    ): void {
        try {
            $result = $value->reconcile($other);
            self::assertNotNull($expected);
            self::assertTrue($expected->equals($result));
        } catch (Exception $exception) {
            self::assertNull($expected);
            self::assertInstanceOf(VariableCanNotBeExtracted::class, $exception);
        }
    }

    public static function provideReconciliations(): iterable
    {
        yield 'same complete values' => [
            ExtractedValue::fromString('john'),
            ExtractedValue::fromString('john'),
            ExtractedValue::fromString('john'),
        ];

        yield 'different complete values' => [
            ExtractedValue::fromString('john'),
            ExtractedValue::fromString('jane'),
            null,
        ];

        yield 'partial and complete compatible' => [
            ExtractedValue::fromString('j', 1),
            ExtractedValue::fromString('john'),
            ExtractedValue::fromString('john'),
        ];

        yield 'complete and partial compatible' => [
            ExtractedValue::fromString('john'),
            ExtractedValue::fromString('j', 1),
            ExtractedValue::fromString('john'),
        ];

        yield 'partial and complete incompatible' => [
            ExtractedValue::fromString('j', 1),
            ExtractedValue::fromString('mary'),
            null,
        ];

        yield 'shorter partial prefix' => [
            ExtractedValue::fromString('jo', 2),
            ExtractedValue::fromString('john', 4),
            ExtractedValue::fromString('john', 4),
        ];

        yield 'longer partial prefix' => [
            ExtractedValue::fromString('john', 4),
            ExtractedValue::fromString('jo', 2),
            ExtractedValue::fromString('john', 4),
        ];

        yield 'same partial values' => [
            ExtractedValue::fromString('jo', 2),
            ExtractedValue::fromString('jo', 2),
            ExtractedValue::fromString('jo', 2),
        ];

        yield 'different partial values with same maximum' => [
            ExtractedValue::fromString('jo', 2),
            ExtractedValue::fromString('ja', 2),
            null,
        ];

        yield 'partial values with incompatible prefixes' => [
            ExtractedValue::fromString('john', 4),
            ExtractedValue::fromString('mary', 4),
            null,
        ];

        yield 'array values can only reconcile with identical complete arrays' => [
            ExtractedValue::fromArray(['one', 'two']),
            ExtractedValue::fromArray(['one', 'two']),
            ExtractedValue::fromArray(['one', 'two']),
        ];

        yield 'different arrays' => [
            ExtractedValue::fromArray(['one', 'two']),
            ExtractedValue::fromArray(['one', 'three']),
            null,
        ];

        yield 'array and scalar' => [
            ExtractedValue::fromArray(['one']),
            ExtractedValue::fromString('one'),
            null,
        ];

        yield 'both missing values' => [
            ExtractedValue::fromNull(),
            ExtractedValue::fromNull(),
            ExtractedValue::fromNull(),
        ];

        yield 'missing and complete value' => [
            ExtractedValue::fromNull(),
            ExtractedValue::fromString('john'),
            null,
        ];

        yield 'complete and missing value' => [
            ExtractedValue::fromString('john'),
            ExtractedValue::fromNull(),
            null,
        ];

        yield 'missing and array value' => [
            ExtractedValue::fromNull(),
            ExtractedValue::fromArray(['one', 'two']),
            null,
        ];

        yield 'array and missing value' => [
            ExtractedValue::fromArray(['one', 'two']),
            ExtractedValue::fromNull(),
            null,
        ];

        yield 'same arrays in different order' => [
            ExtractedValue::fromArray(['one', 'two']),
            ExtractedValue::fromArray(['two', 'one']),
            null,
        ];

        yield 'array is a subset of another array' => [
            ExtractedValue::fromArray(['one']),
            ExtractedValue::fromArray(['one', 'two']),
            null,
        ];

        yield 'array contains an extra value' => [
            ExtractedValue::fromArray(['one', 'two']),
            ExtractedValue::fromArray(['one']),
            null,
        ];

        yield 'arrays with different values' => [
            ExtractedValue::fromArray(['one', 'two']),
            ExtractedValue::fromArray(['one', 'three']),
            null,
        ];
    }
}
