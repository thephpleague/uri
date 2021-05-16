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

use PHPUnit\Framework\TestCase;
use function var_export;

final class IdnaInfoTest extends TestCase
{
    public function testDomainInternalPhpMethod(): void
    {
        $infos = ['result' => 'foo.bar', 'isTransitionalDifferent' => false, 'errors' => 0];
        $result = IdnaInfo::fromIntl($infos);
        /** @var IdnaInfo $generateResult */
        $generateResult = eval('return '.var_export($result, true).';');

        self::assertEquals($result, $generateResult);
    }

    public function testItCanBeInstantiatedFromArray(): void
    {
        $infos = ['result' => '', 'isTransitionalDifferent' => false, 'errors' => 0];
        $result = IdnaInfo::fromIntl($infos);

        self::assertSame('', $result->result());
        self::assertFalse($result->isTransitionalDifferent());
        self::assertSame(0, $result->errors());
        self::assertNull($result->error(Idna::ERROR_BIDI));
        self::assertCount(0, $result->errorList());
    }

    public function testInvalidSyntaxAfterIDNConversion(): void
    {
        try {
            Idna::toAscii('％００.com', Idna::IDNA2008_ASCII);
        } catch (IdnaConversionFailed $exception) {
            $result = $exception->idnaInfo();
            self::assertInstanceOf(IdnaInfo::class, $result);
            self::assertSame(Idna::ERROR_DISALLOWED, $result->errors());
            self::assertIsString($result->error(Idna::ERROR_DISALLOWED));
            self::assertCount(1, $result->errorList());
        }
    }
}
