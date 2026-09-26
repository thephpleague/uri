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

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
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
            'term' => ExtractedValue::fromString('john'),
            'tags' => ExtractedValue::fromArray(['one', 'two']),
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
            'term' => ExtractedValue::fromString('j', 1),
        ]);

        self::assertSame(['term' => 'j'], $result->variables());

        $value = $result->fetch('term');
        self::assertInstanceOf(ExtractedValue::class, $value);
        self::assertTrue($value->isPartial);
    }

    #[Test]
    public function it_fetches_an_extracted_value(): void
    {
        $value = ExtractedValue::fromString('j', 1);
        $result = ExtractionResult::success(['term' => $value]);

        self::assertSame($value, $result->fetch('term'));
        self::assertNull($result->fetch('missing'));
    }

    #[Test]
    public function it_returns_a_value(): void
    {
        $result = ExtractionResult::success([
            'term' => ExtractedValue::fromString('john'),
            'tags' => ExtractedValue::fromArray(['one', 'two']),
        ]);

        self::assertSame('john', $result['term']);
        self::assertSame(['one', 'two'], $result['tags']);
        self::assertNull($result['missing']);
    }

    #[Test]
    public function it_checks_if_a_variable_exists(): void
    {
        $result = ExtractionResult::success([
            'term' => ExtractedValue::fromString('john'),
        ]);

        self::assertTrue(isset($result['term']));
        self::assertFalse(isset($result['missing']));
    }

    #[Test]
    public function it_counts_extracted_values(): void
    {
        $result = ExtractionResult::success([
            'term' => ExtractedValue::fromString('john'),
            'limit' => ExtractedValue::fromString('10'),
        ]);

        self::assertCount(2, $result);
    }

    #[Test]
    public function it_reconciles_results(): void
    {
        $result = ExtractionResult::success([
            'term' => ExtractedValue::fromString('j', 1),
        ]);

        $other = ExtractionResult::success([
            'term' => ExtractedValue::fromString('john'),
            'limit' => ExtractedValue::fromString('10'),
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
            'term' => ExtractedValue::fromString('john'),
        ]);

        $other = ExtractionResult::success([
            'term' => ExtractedValue::fromString('mary'),
        ]);

        $this->expectException(VariableCanNotBeExtracted::class);
        $result->reconcile($other);
    }

    #[Test]
    public function it_accepts_any_iterable(): void
    {
        $values = (static function (): iterable {
            yield 'term' => ExtractedValue::fromString('john');
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
        $result = ExtractionResult::success(['forty-two' => ExtractedValue::fromString('john')]);

        $this->expectException(LogicException::class);

        $result['foobar'] = 'baz';
    }

    #[Test]
    public function it_reject_unsetting_variables_using_array_notation(): void
    {
        $result = ExtractionResult::success(['forty-two' => ExtractedValue::fromString('john')]);

        $this->expectException(LogicException::class);

        unset($result['forty-two']);
    }
}
