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

use DateTimeImmutable;
use DateTimeZone;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

#[CoversClass(ExtractedValue::class)]
#[CoversClass(ExtractionResult::class)]
final class ExtractionResultTest extends TestCase
{
    #[Test]
    public function it_returns_extracted_values(): void
    {
        $result = ExtractionResult::success([
            'term' => new ExtractedValue('john'),
            'tags' => new ExtractedValue(['one', 'two']),
        ]);

        self::assertSame([
            'term' => 'john',
            'tags' => ['one', 'two'],
        ], $result->variables());
        self::assertFalse($result->isEmpty());
    }

    #[Test]
    public function it_returns_partial_values_without_exposing_the_metadata(): void
    {
        $result = ExtractionResult::success([
            'term' => new ExtractedValue('j', 1),
        ]);

        self::assertSame(['term' => 'j'], $result->variables());

        $value = $result->fetch('term');
        self::assertInstanceOf(ExtractedValue::class, $value);
        self::assertTrue($value->isPartial);
    }

    #[Test]
    public function it_fetches_an_extracted_value(): void
    {
        $value = new ExtractedValue('j', 1);
        $result = ExtractionResult::success(['term' => $value]);

        self::assertSame($value, $result->fetch('term'));
        self::assertNull($result->fetch('missing'));
    }

    #[Test]
    public function it_returns_a_value(): void
    {
        $result = ExtractionResult::success([
            'term' => new ExtractedValue('john'),
            'tags' => new ExtractedValue(['one', 'two']),
        ]);

        self::assertSame('john', $result['term']);
        self::assertSame(['one', 'two'], $result['tags']);
        self::assertNull($result['missing']);
    }

    #[Test]
    public function it_checks_if_a_variable_exists(): void
    {
        $result = ExtractionResult::success([
            'term' => new ExtractedValue('john'),
        ]);

        self::assertTrue(isset($result['term']));
        self::assertFalse(isset($result['missing']));
    }

    #[Test]
    public function it_counts_extracted_values(): void
    {
        $result = ExtractionResult::success([
            'term' => new ExtractedValue('john'),
            'limit' => new ExtractedValue('10'),
        ]);

        self::assertCount(2, $result);
    }

    #[Test]
    public function it_reconciles_results(): void
    {
        $result = ExtractionResult::success([
            'term' => new ExtractedValue('j', 1),
        ]);

        $other = ExtractionResult::success([
            'term' => new ExtractedValue('john'),
            'limit' => new ExtractedValue('10'),
        ]);

        $reconciled = $result->reconcile($other);

        self::assertSame([
            'term' => 'john',
            'limit' => '10',
        ], $reconciled->variables());

        $value = $reconciled->fetch('term');
        self::assertInstanceOf(ExtractedValue::class, $value);
        self::assertFalse($value->isPartial);
    }

    #[Test]
    public function it_throws_when_results_cannot_be_reconciled(): void
    {
        $result = ExtractionResult::success([
            'term' => new ExtractedValue('john'),
        ]);

        $other = ExtractionResult::success([
            'term' => new ExtractedValue('mary'),
        ]);

        $this->expectException(VariableCanNotBeExtracted::class);
        $result->reconcile($other);
    }

    #[Test]
    public function it_accepts_any_iterable(): void
    {
        $values = (static function (): iterable {
            yield 'term' => new ExtractedValue('john');
        })();

        $result = ExtractionResult::success($values);

        self::assertSame(['term' => 'john'], $result->variables());
        self::assertTrue($result->isSuccessful());
    }

    #[Test]
    public function it_rejects_invalid_variable_values(): void
    {
        $this->expectException(TypeError::class);

        /** @var iterable<string, mixed> $values */
        $values = ['term' => 'john'];

        ExtractionResult::success($values);
    }

    #[Test]
    public function it_can_be_generated_from_failure(): void
    {
        $exception = VariableCanNotBeExtracted::dueTo('this is an exception', ExtractionErrorReason::PrefixMismatch, ExtractionErrorReason::UnmatchedContent);
        $result = ExtractionResult::failure($exception, Template::new('/foo/bar/{baz}'));

        self::assertFalse($result->isSuccessful());
        self::assertCount(2, $result->reasons());
        self::assertEmpty($result->missingNames());
    }

    #[Test]
    public function it_reject_setting_variables_using_array_notation(): void
    {
        $result = ExtractionResult::success(['forty-two' => new ExtractedValue('john')]);

        $this->expectException(LogicException::class);

        $result['foobar'] = 'baz';
    }

    #[DataProvider('provideIntegerValues')]
    public function testInteger(string $value, ?int $expected): void
    {
        $result = ExtractionResult::success(['value' => new ExtractedValue($value)]);

        self::assertSame($expected, $result->integer('value'));
    }

    public static function provideIntegerValues(): iterable
    {
        yield 'positive integer' => ['42', 42];
        yield 'negative integer' => ['-42', -42];
        yield 'zero' => ['0', 0];
        yield 'positive sign' => ['+42', 42];
        yield 'minimum 64 bit integer' => [(string) PHP_INT_MIN, PHP_INT_MIN];
        yield 'maximum 64 bit integer' => [(string) PHP_INT_MAX, PHP_INT_MAX];

        yield 'decimal value' => ['42.0', null];
        yield 'leading decimal point' => ['.42', null];
        yield 'trailing decimal point' => ['42.', null];
        yield 'scientific notation' => ['4.2e1', null];
        yield 'non numeric value' => ['foo', null];
        yield 'empty string' => ['', null];
        yield 'whitespace' => [' 42', 42];
        yield 'integer with suffix' => ['42foo', null];
    }

    #[DataProvider('provideNonScalarValues')]
    public function testIntegerRejectsNonStringValues(ExtractedValue $value): void
    {
        $result = ExtractionResult::success(['value' => $value]);

        self::assertNull($result->integer('value'));
    }

    public static function provideNonScalarValues(): iterable
    {
        yield 'missing value' => [new ExtractedValue(null)];
        yield 'array value' => [new ExtractedValue(['42'])];
    }

    public function testIntegerReturnsNullForUnknownName(): void
    {
        $result = ExtractionResult::success([]);

        self::assertNull($result->integer('unknown'));
    }

    public function testIntegerReturnsDefaultForInvalidValue(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue('invalid'),
        ]);

        self::assertSame(123, $result->integer('value', 123));
    }

    public function testIntegerReturnsDefaultForMissingValue(): void
    {
        $result = ExtractionResult::success();

        self::assertSame(123, $result->integer('value', 123));
    }

    #[DataProvider('provideFloatValues')]
    public function testFloat(string $value, ?float $expected): void
    {
        $result = ExtractionResult::success(['value' => new ExtractedValue($value)]);

        self::assertSame($expected, $result->float('value'));
    }

    public static function provideFloatValues(): iterable
    {
        yield 'integer representation' => ['42', 42.0];
        yield 'decimal' => ['42.5', 42.5];
        yield 'negative decimal' => ['-42.5', -42.5];
        yield 'zero' => ['0', 0.0];
        yield 'negative zero' => ['-0', -0.0];
        yield 'scientific notation' => ['4.2e1', 42.0];
        yield 'negative scientific notation' => ['-4.2e1', -42.0];

        yield 'non numeric value' => ['foo', null];
        yield 'empty string' => ['', null];
        yield 'whitespace' => [' 42.5', 42.5];
        yield 'float with suffix' => ['42.5foo', null];
    }

    public function testFloatReturnsDefaultForInvalidValue(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue('invalid'),
        ]);

        self::assertSame(12.5, $result->float('value', 12.5));
    }

    public function testBooleanReturnsDefaultForInvalidValue(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue('invalid'),
        ]);

        self::assertTrue($result->boolean('value', true));
    }

    #[DataProvider('provideBooleanValues')]
    public function testBoolean(string $value, ?bool $expected): void
    {
        $result = ExtractionResult::success(['value' => new ExtractedValue($value)]);

        self::assertSame($expected, $result->boolean('value'));
    }

    public static function provideBooleanValues(): iterable
    {
        yield '1' => ['1', true];
        yield 'true' => ['true', true];
        yield 'TRUE' => ['TRUE', true];
        yield 'on' => ['on', true];
        yield 'yes' => ['yes', true];

        yield '0' => ['0', false];
        yield 'false' => ['false', false];
        yield 'FALSE' => ['FALSE', false];
        yield 'off' => ['off', false];
        yield 'no' => ['no', false];

        yield 'empty string' => ['', false];
        yield 'random string' => ['foo', null];
        yield 'integer-looking string' => ['2', null];
        yield 'whitespace' => [' true', true];
    }

    public function testIntegerDoesNotReturnDefaultForZero(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue('0'),
        ]);

        self::assertSame(0, $result->integer('value', 42));
    }

    public function testBooleanDoesNotReturnDefaultForFalse(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue('false'),
        ]);

        self::assertFalse($result->boolean('value', true));
    }

    public function testEnumReturnsBackedEnumFromItsValue(): void
    {
        $result = ExtractionResult::success(['status' => new ExtractedValue('published')]);

        self::assertSame(Status::Published, $result->enum('status', Status::class));
    }

    public function testEnumDoesNotMatchBackedEnumCaseName(): void
    {
        $result = ExtractionResult::success(['status' => new ExtractedValue('Published')]);

        self::assertNull($result->enum('status', Status::class));
    }

    public function testEnumReturnsUnitEnumFromItsCaseName(): void
    {
        $result = ExtractionResult::success(['visibility' => new ExtractedValue('Public')]);

        self::assertSame(Visibility::Public, $result->enum('visibility', Visibility::class));
    }

    public function testEnumDoesNotMatchUnitEnumCaseValue(): void
    {
        $result = ExtractionResult::success(['visibility' => new ExtractedValue('public')]);

        self::assertNull($result->enum('visibility', Visibility::class));
    }

    public function testEnumReturnsNullForUnknownCase(): void
    {
        $result = ExtractionResult::success([
            'status' => new ExtractedValue('unknown'),
        ]);

        self::assertNull($result->enum('status', Status::class));
    }

    public function testEnumReturnsNullForUnknownEnumClass(): void
    {
        $result = ExtractionResult::success([
            'status' => new ExtractedValue('published'),
        ]);

        self::assertNull($result->enum('status', 'UnknownEnum')); /* @phpstan-ignore-line */
    }

    #[DataProvider('provideInvalidEnumValues')]
    public function testEnumRejectsNonStringValues(ExtractedValue $value): void
    {
        $result = ExtractionResult::success(['status' => $value]);

        self::assertNull($result->enum('status', Status::class));
    }

    public static function provideInvalidEnumValues(): iterable
    {
        yield 'missing' => [new ExtractedValue(null)];
        yield 'array' => [new ExtractedValue(['published'])];
    }

    public function testDate(): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue('2026-09-17'),
        ]);

        self::assertEquals(
            new DateTimeImmutable('2026-09-17', new DateTimeZone('UTC')),
            $result->date('date', '!Y-m-d'),
        );
    }

    public function testDateUsesUtcByDefault(): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue('2026-09-17 12:30:00'),
        ]);

        $date = $result->date('date', '!Y-m-d H:i:s');

        self::assertNotNull($date);
        self::assertSame('UTC', $date->getTimezone()->getName());
    }

    public function testDateAcceptsTimezoneObject(): void
    {
        $timezone = new DateTimeZone('Europe/Brussels');

        $result = ExtractionResult::success([
            'date' => new ExtractedValue('2026-09-17 12:30:00'),
        ]);

        $date = $result->date('date', '!Y-m-d H:i:s', $timezone);

        self::assertNotNull($date);
        self::assertSame($timezone->getName(), $date->getTimezone()->getName());
    }

    public function testDateAcceptsTimezoneName(): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue('2026-09-17 12:30:00'),
        ]);

        $date = $result->date('date', '!Y-m-d H:i:s', 'Europe/Brussels');

        self::assertNotNull($date);
        self::assertSame('Europe/Brussels', $date->getTimezone()->getName());
    }

    public function testDateReturnsNullForInvalidTimezone(): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue('2026-09-17'),
        ]);

        self::assertNull(
            $result->date('date', 'Y-m-d', 'Not/A/Timezone'),
        );
    }

    #[DataProvider('provideInvalidDateFormats')]
    public function testDateReturnsNullForInvalidFormat(string $format): void
    {
        $result = ExtractionResult::success(['date' => new ExtractedValue('2026-09-17')]);

        self::assertNull($result->date('date', $format));  /* @phpstan-ignore-line */
    }

    public static function provideInvalidDateFormats(): iterable
    {
        yield 'empty format' => [''];
        yield 'whitespace only' => ['   '];
    }

    #[DataProvider('provideInvalidDates')]
    public function testDateRejectsInvalidDates(string $value): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue($value),
        ]);

        self::assertNull($result->date('date', 'Y-m-d'));
    }

    public static function provideInvalidDates(): iterable
    {
        yield 'invalid day' => ['2026-01-32'];
        yield 'invalid month' => ['2026-13-01'];
        yield 'invalid leap day' => ['2026-02-29'];
    }

    public function testDateAcceptsValidLeapDay(): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue('2024-02-29'),
        ]);

        self::assertEquals(
            new DateTimeImmutable('2024-02-29', new DateTimeZone('UTC')),
            $result->date('date', '!Y-m-d'),
        );
    }

    public function testDateRejectsNullByteInValue(): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue("2026-09-17\0"),
        ]);

        self::assertNull($result->date('date', 'Y-m-d'));
    }

    public function testDateRejectsNullByteInFormat(): void
    {
        $result = ExtractionResult::success(['date' => new ExtractedValue("2026-09-17\0")]);

        self::assertNull($result->date('date', 'Y-m-d'));
    }

    #[DataProvider('provideInvalidExtractedValues')]
    public function testDateRejectsNonStringValues(ExtractedValue $value): void
    {
        $result = ExtractionResult::success([
            'date' => $value,
        ]);

        self::assertNull($result->date('date', 'Y-m-d'));
    }

    public static function provideInvalidExtractedValues(): iterable
    {
        yield 'missing value' => [new ExtractedValue(null)];
        yield 'array value' => [new ExtractedValue(['2026-09-17'])];
    }

    public function testDateReturnsNullForUnknownName(): void
    {
        $result = ExtractionResult::success([]);

        self::assertNull($result->date('unknown', 'Y-m-d'));
    }

    public function testDateTrimsTheFormat(): void
    {
        $result = ExtractionResult::success([
            'date' => new ExtractedValue('2026-09-17'),
        ]);

        self::assertNotNull(
            $result->date('date', '  Y-m-d  '),
        );
    }

    public function testStringReturnsExtractedString(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue('hello'),
        ]);

        self::assertSame('hello', $result->string('value'));
    }

    public function testStringReturnsEmptyString(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue(''),
        ]);

        self::assertSame('', $result->string('value'));
    }

    public function testStringReturnsDefaultForMissingValue(): void
    {
        $result = ExtractionResult::success([]);

        self::assertSame('default', $result->string('value', 'default'));
    }

    public function testStringReturnsNullForMissingValueByDefault(): void
    {
        $result = ExtractionResult::success([]);

        self::assertNull($result->string('value'));
    }

    public function testStringReturnsDefaultForNullValue(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue(null),
        ]);

        self::assertSame('default', $result->string('value', 'default'));
    }

    public function testStringReturnsDefaultForArrayValue(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue(['one', 'two']),
        ]);

        self::assertSame('default', $result->string('value', 'default'));
    }

    public function testStringReturnsNullForArrayValueByDefault(): void
    {
        $result = ExtractionResult::success([
            'value' => new ExtractedValue(['one', 'two']),
        ]);

        self::assertNull($result->string('value'));
    }

    public function testStringReturnsDefaultForUnknownName(): void
    {
        $result = ExtractionResult::success([
            'other' => new ExtractedValue('value'),
        ]);

        self::assertSame('default', $result->string('value', 'default'));
    }

    #[DataProvider('provideNonStringValues')]
    public function testStringRejectsNonStringValues(
        ExtractedValue $value,
        ?string $default,
    ): void {
        $result = ExtractionResult::success([
            'value' => $value,
        ]);

        self::assertSame($default, $result->string('value', $default));
    }

    public static function provideNonStringValues(): iterable
    {
        yield 'missing value' => [new ExtractedValue(null), 'default'];
        yield 'array value' => [new ExtractedValue(['one', 'two']), 'default'];
        yield 'missing value without default' => [new ExtractedValue(null), null];
        yield 'array value without default' => [new ExtractedValue(['one', 'two']), null];
    }
}

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}

enum Visibility
{
    case Public;
    case Private;
}
