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

namespace League\Uri;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;
use ValueError;

use function array_keys;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

#[CoversClass(TypeConverter::class)]
final class TypeConverterTest extends TestCase
{
    #[DataProvider('stringProvider')]
    public function testToString(mixed $value, ?string $expected): void
    {
        self::assertSame($expected, TypeConverter::toString($value));
    }

    public static function stringProvider(): iterable
    {
        yield 'string' => ['foo', 'foo'];
        yield 'empty string' => ['', ''];
        yield 'integer' => [42, '42'];
        yield 'negative integer' => [-42, '-42'];
        yield 'zero' => [0, '0'];
        yield 'float' => [42.5, '42.5'];
        yield 'boolean true' => [true, '1'];
        yield 'boolean false' => [false, ''];
        yield 'stringable' => [
            new class () implements Stringable {
                public function __toString(): string
                {
                    return 'foo';
                }
            },
            'foo',
        ];
        yield 'null' => [null, null];
        yield 'array' => [['foo'], null];
        yield 'object' => [new stdClass(), null];
        yield 'backed enum' => [TestBackedEnum::Foo, 'foo'];
    }

    public function testToStringsReturnsConvertedValues(): void
    {
        self::assertSame(
            ['foo', '42', '42.5', '1', ''],
            TypeConverter::toStrings(['foo', 42, 42.5, true, false]),
        );
    }

    public function testToStringsPreservesKeys(): void
    {
        self::assertSame(
            ['foo' => '42', 'bar' => '43'],
            TypeConverter::toStrings([
                'foo' => 42,
                'bar' => 43,
            ]),
        );
    }

    public function testToStringsReturnsEmptyArrayForNonIterableValue(): void
    {
        self::assertSame([], TypeConverter::toStrings('foo'));
    }

    public function testToStringsReturnsEmptyArrayWhenConversionFails(): void
    {
        self::assertSame(
            [],
            TypeConverter::toStrings(['foo', ['bar']]),
        );
    }

    public function testToStringsUsesDefaultForInvalidValues(): void
    {
        self::assertSame(
            ['foo', 'default', 'bar'],
            TypeConverter::toStrings(['foo', ['bar'], 'bar'], 'default'),
        );
    }

    #[DataProvider('integerProvider')]
    public function testToInteger(mixed $value, ?int $expected): void
    {
        self::assertSame($expected, TypeConverter::toInteger($value));
    }

    public static function integerProvider(): iterable
    {
        yield 'integer' => [42, 42];
        yield 'negative integer' => [-42, -42];
        yield 'zero' => [0, 0];
        yield 'positive sign' => ['+42', 42];
        yield 'negative sign' => ['-42', -42];
        yield 'integer string' => ['42', 42];
        yield 'minimum integer' => [(string) PHP_INT_MIN, PHP_INT_MIN];
        yield 'maximum integer' => [(string) PHP_INT_MAX, PHP_INT_MAX];
        yield 'leading whitespace' => [' 42', 42];
        yield 'backed enum' => [TestIntBackedEnum::FortyTwo, 42];

        yield 'float' => [42.0, null];
        yield 'decimal' => ['42.0', null];
        yield 'fraction' => ['.42', null];
        yield 'trailing decimal point' => ['42.', null];
        yield 'scientific notation' => ['4.2e1', null];
        yield 'non numeric string' => ['foo', null];
        yield 'empty string' => ['', null];
        yield 'numeric suffix' => ['42foo', null];
        yield 'null' => [null, null];
        yield 'array' => [['42'], null];
        yield 'object' => [new stdClass(), null];
    }

    public function testToIntegersReturnsConvertedValues(): void
    {
        self::assertSame(
            [42, -42, 0],
            TypeConverter::toIntegers(['42', '-42', '0']),
        );
    }

    public function testToIntegersPreservesKeys(): void
    {
        self::assertSame(
            ['foo' => 42, 'bar' => 43],
            TypeConverter::toIntegers([
                'foo' => '42',
                'bar' => '43',
            ]),
        );
    }

    public function testToIntegersReturnsEmptyArrayForNonIterableValue(): void
    {
        self::assertSame([], TypeConverter::toIntegers('42'));
    }

    public function testToIntegersReturnsEmptyArrayWhenConversionFails(): void
    {
        self::assertSame(
            [],
            TypeConverter::toIntegers(['42', 'foo', '43']),
        );
    }

    public function testToIntegersUsesDefaultForInvalidValues(): void
    {
        self::assertSame(
            [42, 0, 43],
            TypeConverter::toIntegers(['42', 'foo', '43'], 0),
        );
    }

    public function testToIntegersDoesNotReplaceZeroWithDefault(): void
    {
        self::assertSame(
            [0],
            TypeConverter::toIntegers(['0'], 42),
        );
    }

    #[DataProvider('floatProvider')]
    public function testToFloat(mixed $value, ?float $expected): void
    {
        self::assertSame($expected, TypeConverter::toFloat($value));
    }

    public static function floatProvider(): iterable
    {
        yield 'float' => [42.5, 42.5];
        yield 'negative float' => [-42.5, -42.5];
        yield 'zero integer' => [0, 0.0];
        yield 'negative zero integer' => [-0, 0.0];
        yield 'integer' => [42, 42.0];
        yield 'integer string' => ['42', 42.0];
        yield 'decimal string' => ['42.5', 42.5];
        yield 'negative decimal string' => ['-42.5', -42.5];
        yield 'scientific notation' => ['4.2e1', 42.0];
        yield 'leading whitespace' => [' 42.5', 42.5];
        yield 'backed enum' => [TestIntBackedEnum::FortyTwo, 42.0];

        yield 'non numeric string' => ['foo', null];
        yield 'empty string' => ['', null];
        yield 'numeric suffix' => ['42foo', null];
        yield 'null' => [null, null];
        yield 'array' => [['42'], null];
        yield 'object' => [new stdClass(), null];
    }

    public function testToFloatsReturnsConvertedValues(): void
    {
        self::assertSame(
            [42.0, -42.5, 0.0],
            TypeConverter::toFloats(['42', -42.5, 0]),
        );
    }

    public function testToFloatsPreservesKeys(): void
    {
        self::assertSame(
            ['foo' => 42.0, 'bar' => 43.5],
            TypeConverter::toFloats([
                'foo' => '42',
                'bar' => '43.5',
            ]),
        );
    }

    public function testToFloatsReturnsEmptyArrayForNonIterableValue(): void
    {
        self::assertSame([], TypeConverter::toFloats('42'));
    }

    public function testToFloatsReturnsEmptyArrayWhenConversionFails(): void
    {
        self::assertSame(
            [],
            TypeConverter::toFloats(['42', 'foo', '43']),
        );
    }

    public function testToFloatsUsesDefaultForInvalidValues(): void
    {
        self::assertSame(
            [42.0, 0.0, 43.0],
            TypeConverter::toFloats(['42', 'foo', '43'], 0.0),
        );
    }

    #[DataProvider('booleanProvider')]
    public function testToBoolean(mixed $value, ?bool $expected): void
    {
        self::assertSame($expected, TypeConverter::toBoolean($value));
    }

    public static function booleanProvider(): iterable
    {
        yield 'true' => ['true', true];
        yield 'TRUE' => ['TRUE', true];
        yield 'one' => ['1', true];
        yield 'one integer' => [1, true];
        yield 'on' => ['on', true];
        yield 'yes' => ['yes', true];
        yield 'leading whitespace true' => [' true', true];
        yield 'backed enum true' => [TestIntBackedEnum::One, true];

        yield 'false' => ['false', false];
        yield 'FALSE' => ['FALSE', false];
        yield 'zero' => ['0', false];
        yield 'zero integer' => [0, false];
        yield 'off' => ['off', false];
        yield 'no' => ['no', false];
        yield 'empty string' => ['', false];
        yield 'backed enum false' => [TestIntBackedEnum::Zero, false];

        yield 'integer' => [2, null];
        yield 'null' => [null, null];
        yield 'array' => [['true'], null];
        yield 'object' => [new stdClass(), null];
        yield 'unknown string' => ['foo', null];
        yield 'backed enum null' => [TestIntBackedEnum::FortyTwo, null];
    }

    public function testToBooleansReturnsConvertedValues(): void
    {
        self::assertSame(
            [true, false, true],
            TypeConverter::toBooleans(['true', 'false', 'yes']),
        );
    }

    public function testToBooleansPreservesKeys(): void
    {
        self::assertSame(
            ['foo' => true, 'bar' => false],
            TypeConverter::toBooleans([
                'foo' => 'true',
                'bar' => 'false',
            ]),
        );
    }

    public function testToBooleansReturnsEmptyArrayForNonIterableValue(): void
    {
        self::assertSame([], TypeConverter::toBooleans('true'));
    }

    public function testToBooleansReturnsEmptyArrayWhenConversionFails(): void
    {
        self::assertSame(
            [],
            TypeConverter::toBooleans(['true', 'foo', 'false']),
        );
    }

    public function testToBooleansUsesDefaultForInvalidValues(): void
    {
        self::assertSame(
            [true, true, false],
            TypeConverter::toBooleans(['true', 'foo', 'false'], true),
        );
    }

    public function testToBooleansDoesNotReplaceFalseWithDefault(): void
    {
        self::assertSame(
            [false],
            TypeConverter::toBooleans(['false'], true),
        );
    }

    public function testToEnumReturnsBackedEnum(): void
    {
        self::assertSame(
            TestBackedEnum::Foo,
            TypeConverter::toEnum('foo', TestBackedEnum::class),
        );
    }

    public function testToEnumReturnsBackedEnumFromIntegerValue(): void
    {
        self::assertSame(
            TestIntBackedEnum::FortyTwo,
            TypeConverter::toEnum(42, TestIntBackedEnum::class),
        );
    }

    public function testToEnumConvertsStringToIntegerBackedEnumValue(): void
    {
        self::assertSame(
            TestIntBackedEnum::FortyTwo,
            TypeConverter::toEnum('42', TestIntBackedEnum::class),
        );
    }

    public function testToEnumConvertsIntegerToStringBackedEnumValue(): void
    {
        self::assertSame(
            TestBackedEnum::Number42,
            TypeConverter::toEnum(42, TestBackedEnum::class),
        );
    }

    public function testToEnumReturnsSameInstance(): void
    {
        $enum = TestBackedEnum::Foo;

        self::assertSame(
            $enum,
            TypeConverter::toEnum($enum, TestBackedEnum::class),
        );
    }

    public function testToEnumDoesNotAcceptCaseNameForBackedEnum(): void
    {
        self::assertNull(
            TypeConverter::toEnum('Foo', TestBackedEnum::class),
        );
    }

    public function testToEnumReturnsUnitEnumByCaseName(): void
    {
        self::assertSame(
            TestBasicUnitEnum::Foo,
            TypeConverter::toEnum('Foo', TestBasicUnitEnum::class),
        );
    }

    public function testToEnumDoesNotUseBackingLikeValueForUnitEnum(): void
    {
        self::assertNull(
            TypeConverter::toEnum('foo', TestBasicUnitEnum::class),
        );
    }

    public function testToEnumReturnsNullForUnknownCase(): void
    {
        self::assertNull(
            TypeConverter::toEnum('unknown', TestBackedEnum::class),
        );
    }

    public function testToEnumReturnsNullForUnknownEnumClass(): void
    {
        $this->expectException(ValueError::class);

        /* @phpstan-ignore-next-line */;
        TypeConverter::toEnum('foo', 'UnknownEnum');
    }

    public function testToEnumReturnsNullForInvalidValue(): void
    {
        self::assertNull(
            TypeConverter::toEnum(null, TestBackedEnum::class),
        );

        self::assertNull(
            TypeConverter::toEnum(['foo'], TestBackedEnum::class),
        );
    }

    public function testToEnumsReturnsConvertedValues(): void
    {
        self::assertSame(
            [
                TestBackedEnum::Foo,
                TestBackedEnum::Number42,
            ],
            TypeConverter::toEnums(
                ['foo', '42'],
                TestBackedEnum::class,
            ),
        );
    }

    public function testToEnumsPreservesKeys(): void
    {
        self::assertSame(
            [
                'foo' => TestBackedEnum::Foo,
                'bar' => TestBackedEnum::Number42,
            ],
            TypeConverter::toEnums(
                [
                    'foo' => 'foo',
                    'bar' => '42',
                ],
                TestBackedEnum::class,
            ),
        );
    }

    public function testToEnumsReturnsEmptyArrayForNonIterableValue(): void
    {
        self::assertSame(
            [],
            TypeConverter::toEnums('foo', TestBackedEnum::class),
        );
    }

    public function testToEnumsReturnsEmptyArrayWhenConversionFails(): void
    {
        self::assertSame(
            [],
            TypeConverter::toEnums(
                ['foo', 'unknown'],
                TestBackedEnum::class,
            ),
        );
    }

    public function testToEnumsReturnsTheDefaultWhenConversionFails(): void
    {
        self::assertSame(
            [
                TestBackedEnum::Foo,
                TestBackedEnum::Number42,
            ],
            TypeConverter::toEnums(
                ['foo', 42.5],
                TestBackedEnum::class,
                TestBackedEnum::Number42,
            ),
        );
    }

    public function testToDateTimeImmutableReturnsDate(): void
    {
        $date = TypeConverter::toDateTimeImmutable(
            TestBackedEnum::Date,
            '!Y-m-d',
        );

        self::assertInstanceOf(DateTimeImmutable::class, $date);
        self::assertSame('2026-09-18', $date->format('Y-m-d'));
    }

    public function testToDateTimeImmutableUsesUtcByDefault(): void
    {
        $date = TypeConverter::toDateTimeImmutable(
            '2026-09-18',
            '!Y-m-d',
        );

        self::assertNotNull($date);
        self::assertSame('UTC', $date->getTimezone()->getName());
    }

    public function testToDateTimeImmutableAcceptsDateTimeZone(): void
    {
        $timezone = new DateTimeZone('Europe/Brussels');

        $date = TypeConverter::toDateTimeImmutable(
            '2026-09-18',
            '!Y-m-d',
            $timezone,
        );

        self::assertNotNull($date);
        self::assertSame($timezone->getName(), $date->getTimezone()->getName());
    }

    public function testToDateTimeImmutableAcceptsTimezoneString(): void
    {
        $date = TypeConverter::toDateTimeImmutable(
            '2026-09-18',
            '!Y-m-d',
            'Europe/Brussels',
        );

        self::assertNotNull($date);
        self::assertSame('Europe/Brussels', $date->getTimezone()->getName());
    }

    public function testToDateTimeImmutableReturnsNullForInvalidTimezone(): void
    {
        $this->expectException(Exception::class);

        TypeConverter::toDateTimeImmutable('2026-09-18', '!Y-m-d', 'invalid/timezone');
    }

    public function testToDateTimeImmutableTrimsFormat(): void
    {
        $date = TypeConverter::toDateTimeImmutable(
            '2026-09-18',
            '  !Y-m-d  ',
        );

        self::assertNotNull($date);
        self::assertSame('2026-09-18', $date->format('Y-m-d'));
    }

    /**
     * @param non-empty-string $format
     */
    #[DataProvider('invalidDateProvider')]
    public function testToDateTimeImmutableRejectsInvalidDates(
        string $value,
        string $format,
    ): void {
        self::assertNull(TypeConverter::toDateTimeImmutable($value, $format));
    }

    public static function invalidDateProvider(): iterable
    {
        yield 'invalid day' => ['2026-01-32', '!Y-m-d'];
        yield 'invalid month' => ['2026-13-01', '!Y-m-d'];
        yield 'invalid leap day' => ['2026-02-29', '!Y-m-d'];
    }

    public function testToDateTimeImmutableAcceptsLeapDay(): void
    {
        $date = TypeConverter::toDateTimeImmutable(
            '2024-02-29',
            '!Y-m-d',
        );

        self::assertNotNull($date);
        self::assertSame('2024-02-29', $date->format('Y-m-d'));
    }

    public function testToDateTimeImmutableRejectsNullByteInValue(): void
    {
        self::assertNull(
            TypeConverter::toDateTimeImmutable(
                "2026-09-18\0",
                '!Y-m-d',
            ),
        );
    }

    public function testToDateTimeImmutableRejectsNullByteInFormat(): void
    {
        self::assertNull(
            TypeConverter::toDateTimeImmutable(
                "2026-09-18\0",
                '!Y-m-d',
            ),
        );
    }

    public function testToDateTimeImmutableRejectsNonStringValue(): void
    {
        self::assertNull(
            TypeConverter::toDateTimeImmutable(
                42,
                '!Y-m-d',
            ),
        );

        self::assertNull(
            TypeConverter::toDateTimeImmutable(
                ['2026-09-18'],
                '!Y-m-d',
            ),
        );
    }

    public function testToDateTimeImmutablesReturnsConvertedValues(): void
    {
        $dates = TypeConverter::toDateTimeImmutables(
            ['2026-09-18', '2026-09-19'],
            '!Y-m-d',
        );

        self::assertCount(2, $dates);
        self::assertSame('2026-09-18', $dates[0]->format('Y-m-d'));
        self::assertSame('2026-09-19', $dates[1]->format('Y-m-d'));
    }

    public function testToDateTimeImmutablesPreservesKeys(): void
    {
        $dates = TypeConverter::toDateTimeImmutables(
            [
                'start' => '2026-09-18',
                'end' => '2026-09-19',
            ],
            '!Y-m-d',
        );

        self::assertSame(
            ['start', 'end'],
            array_keys($dates),
        );

        self::assertSame(
            '2026-09-18',
            $dates['start']->format('Y-m-d'),
        );

        self::assertSame(
            '2026-09-19',
            $dates['end']->format('Y-m-d'),
        );
    }

    public function testToDateTimeImmutablesReturnsEmptyArrayForNonIterableValue(): void
    {
        self::assertSame(
            [],
            TypeConverter::toDateTimeImmutables(
                '2026-09-18',
                '!Y-m-d',
            ),
        );
    }

    public function testToDateTimeImmutablesReturnsEmptyArrayWhenConversionFails(): void
    {
        self::assertSame(
            [],
            TypeConverter::toDateTimeImmutables(
                ['2026-09-18', 'invalid'],
                '!Y-m-d',
            ),
        );
    }

    public function testToDateTimeImmutablesReturnsEmptyArrayForEmptyIterable(): void
    {
        self::assertSame(
            [],
            TypeConverter::toDateTimeImmutables([], '!Y-m-d'),
        );
    }

    public function testToDateTimeImmutablesUsesDefaultForInvalidValues(): void
    {
        $default = new DateTime('2026-01-01');

        $dates = TypeConverter::toDateTimeImmutables(
            ['2026-09-18', 'invalid', '2026-09-19'],
            '!Y-m-d',
            null,
            $default,
        );

        self::assertEquals(
            [
                new DateTimeImmutable('2026-09-18'),
                $default,
                new DateTimeImmutable('2026-09-19'),
            ],
            $dates,
        );

        self::assertContainsOnlyInstancesOf(DateTimeImmutable::class, $dates);
    }

    public function testToDateTimeImmutableThrowsOnEmptyFormat(): void
    {
        $this->expectException(ValueError::class);

        TypeConverter::toDateTimeImmutable('2026-09-18', '     ');
    }

    public function testToEnumsThrowsForDefaultFromAnotherEnum(): void
    {
        $this->expectException(ValueError::class);

        TypeConverter::toEnums(['foo'], TestBackedEnum::class, TestBasicUnitEnum::Foo);
    }
}

enum TestBackedEnum: string
{
    case Foo = 'foo';
    case Number42 = '42';
    case Date = '2026-09-18';
}

enum TestIntBackedEnum: int
{
    case FortyTwo = 42;
    case One = 1;
    case Zero = 0;
}

enum TestBasicUnitEnum
{
    case Foo;
    case Bar;
}
