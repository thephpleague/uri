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
use PHPUnit\Framework\TestCase;
use TypeError;

#[CoversClass(VariableCanNotBeExtracted::class)]
final class VariableCanNotBeExtractedTest extends TestCase
{
    public function testItPreservesTheMessage(): void
    {
        $exception = VariableCanNotBeExtracted::dueTo('Extraction failed.', ExtractionErrorReason::MalformedValue);

        self::assertSame('Extraction failed.', $exception->getMessage());
    }

    public function testItDeduplicatesReasons(): void
    {
        $exception = VariableCanNotBeExtracted::dueTo(
            'Extraction failed.',
            ExtractionErrorReason::ReconciliationFailed,
            ExtractionErrorReason::PrefixLengthExceeded,
            ExtractionErrorReason::ReconciliationFailed,
        );

        self::assertSame(
            [
                ExtractionErrorReason::ReconciliationFailed,
                ExtractionErrorReason::PrefixLengthExceeded,
            ],
            $exception->getReasons(),
        );
    }

    public function testItDeduplicatesMissingVariables(): void
    {
        $exception = VariableCanNotBeExtracted::dueToMissingVariables('this/will/not/work', Template::new('{foo}/bar'), ['foo', 'bar', 'foo']);

        self::assertSame(['foo', 'bar'], $exception->getMissingNames());
    }

    public function testItRejectsInvalidMissingVariableNames(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Missing variable name must be a string; int received.');

        VariableCanNotBeExtracted::dueToMissingVariables('this/will/not/work', Template::new('{foo}/bar'), ['foo', 42]);
    }

    public function testDueToMissingVariables(): void
    {
        $template = Template::new('/{foo}/{bar}');
        $result = ExtractionResult::success([
            'foo' => new ExtractedValue('value'),
            'bar' => new ExtractedValue(null),
        ]);

        $exception = VariableCanNotBeExtracted::dueToMissingVariables('/value/', $template, $result);

        self::assertSame('The value "/value/" does not provide all the variables defined by the template "/{foo}/{bar}"; Missing: "bar".', $exception->getMessage());
        self::assertSame([ExtractionErrorReason::MissingVariables], $exception->getReasons());
        self::assertSame(['bar'], $exception->getMissingNames());
        self::assertSame(['foo', 'bar'], $exception->getNames());
    }
}
