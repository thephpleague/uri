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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtractedValue::class)]
final class ExtractedValueTest extends TestCase
{
    #[DataProvider('provideValues')]
    public function testFromValue(
        string|array $value,
        string $specifier,
        string|array $expectedValue,
        bool $expectedPartial,
    ): void {
        $extracted = ExtractedValue::fromValue(
            $value,
            VarSpecifier::new($specifier),
        );

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

        yield 'complete array' => [
            ['one', 'two'],
            'tags*',
            ['one', 'two'],
            false,
        ];
    }

    public function testPrefixPositionCannotBeAssociatedWithArray(): void
    {
        $this->expectException(VariableCanNotBeExtracted::class);

        new ExtractedValue(['one', 'two'], 2);
    }

    public function testNegativeMaxLengthOtherThanMinusOneIsRejected(): void
    {
        $this->expectException(VariableCanNotBeExtracted::class);

        new ExtractedValue('john', -2);
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
            new ExtractedValue('john'),
            new ExtractedValue('john'),
            true,
        ];

        yield 'same partial value' => [
            new ExtractedValue('j', 1),
            new ExtractedValue('j', 1),
            true,
        ];

        yield 'same value but different maximum length' => [
            new ExtractedValue('john', 4),
            new ExtractedValue('john', 5),
            false,
        ];

        yield 'different values' => [
            new ExtractedValue('john'),
            new ExtractedValue('jane'),
            false,
        ];

        yield 'different types' => [
            new ExtractedValue('john'),
            new ExtractedValue(['john']),
            false,
        ];

        yield 'not an ExtractedValue' => [
            new ExtractedValue('john'),
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
        $result = $value->reconcile($other);

        if (null === $expected) {
            self::assertNull($result);

            return;
        }

        self::assertNotNull($result);
        self::assertTrue($expected->equals($result));
    }

    public static function provideReconciliations(): iterable
    {
        yield 'same complete values' => [
            new ExtractedValue('john'),
            new ExtractedValue('john'),
            new ExtractedValue('john'),
        ];

        yield 'different complete values' => [
            new ExtractedValue('john'),
            new ExtractedValue('jane'),
            null,
        ];

        yield 'partial and complete compatible' => [
            new ExtractedValue('j', 1),
            new ExtractedValue('john'),
            new ExtractedValue('john'),
        ];

        yield 'complete and partial compatible' => [
            new ExtractedValue('john'),
            new ExtractedValue('j', 1),
            new ExtractedValue('john'),
        ];

        yield 'partial and complete incompatible' => [
            new ExtractedValue('j', 1),
            new ExtractedValue('mary'),
            null,
        ];

        yield 'shorter partial prefix' => [
            new ExtractedValue('jo', 2),
            new ExtractedValue('john', 4),
            new ExtractedValue('john', 4),
        ];

        yield 'longer partial prefix' => [
            new ExtractedValue('john', 4),
            new ExtractedValue('jo', 2),
            new ExtractedValue('john', 4),
        ];

        yield 'same partial values' => [
            new ExtractedValue('jo', 2),
            new ExtractedValue('jo', 2),
            new ExtractedValue('jo', 2),
        ];

        yield 'different partial values with same maximum' => [
            new ExtractedValue('jo', 2),
            new ExtractedValue('ja', 2),
            null,
        ];

        yield 'partial values with incompatible prefixes' => [
            new ExtractedValue('john', 4),
            new ExtractedValue('mary', 4),
            null,
        ];

        yield 'array values can only reconcile with identical complete arrays' => [
            new ExtractedValue(['one', 'two']),
            new ExtractedValue(['one', 'two']),
            new ExtractedValue(['one', 'two']),
        ];

        yield 'different arrays' => [
            new ExtractedValue(['one', 'two']),
            new ExtractedValue(['one', 'three']),
            null,
        ];

        yield 'array and scalar' => [
            new ExtractedValue(['one']),
            new ExtractedValue('one'),
            null,
        ];
    }
}
