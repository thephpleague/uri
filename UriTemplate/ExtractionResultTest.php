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
        $result = new ExtractionResult([
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
        $result = new ExtractionResult([
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
        $result = new ExtractionResult(['term' => $value]);

        self::assertSame($value, $result->fetch('term'));
        self::assertNull($result->fetch('missing'));
    }

    #[Test]
    public function it_returns_a_value(): void
    {
        $result = new ExtractionResult([
            'term' => new ExtractedValue('john'),
            'tags' => new ExtractedValue(['one', 'two']),
        ]);

        self::assertSame('john', $result->value('term'));
        self::assertSame(['one', 'two'], $result->value('tags'));
        self::assertNull($result->value('missing'));
    }

    #[Test]
    public function it_checks_if_a_variable_exists(): void
    {
        $result = new ExtractionResult([
            'term' => new ExtractedValue('john'),
        ]);

        self::assertTrue($result->has('term'));
        self::assertFalse($result->has('missing'));
    }

    #[Test]
    public function it_counts_extracted_values(): void
    {
        $result = new ExtractionResult([
            'term' => new ExtractedValue('john'),
            'limit' => new ExtractedValue('10'),
        ]);

        self::assertCount(2, $result);
    }

    #[Test]
    public function it_is_iterable(): void
    {
        $term = new ExtractedValue('john');
        $limit = new ExtractedValue('10');

        $result = new ExtractionResult([
            'term' => $term,
            'limit' => $limit,
        ]);

        self::assertSame([
            'term' => $term,
            'limit' => $limit,
        ], iterator_to_array($result));
    }

    #[Test]
    public function it_reconciles_results(): void
    {
        $result = new ExtractionResult([
            'term' => new ExtractedValue('j', 1),
        ]);

        $other = new ExtractionResult([
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
    public function it_returns_null_when_results_cannot_be_reconciled(): void
    {
        $result = new ExtractionResult([
            'term' => new ExtractedValue('john'),
        ]);

        $other = new ExtractionResult([
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

        $result = new ExtractionResult($values);

        self::assertSame(['term' => 'john'], $result->variables());
    }

    #[Test]
    public function it_rejects_non_string_variable_names(): void
    {
        $this->expectException(TypeError::class);

        /** @var iterable<int, ExtractedValue> $values */
        $values = [42 => new ExtractedValue('john')];

        new ExtractionResult($values); /* @phpstan-ignore-line */
    }

    #[Test]
    public function it_rejects_invalid_variable_values(): void
    {
        $this->expectException(TypeError::class);

        /** @var iterable<string, mixed> $values */
        $values = ['term' => 'john'];

        new ExtractionResult($values); /* @phpstan-ignore-line */
    }
}
