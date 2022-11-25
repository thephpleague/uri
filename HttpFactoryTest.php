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

final class HttpFactoryTest extends TestCase
{
    public function testCreateUri(): void
    {
        $factory = new HttpFactory();
        $uri = $factory->createUri('https://nyholm.tech/foo');

        self::assertInstanceOf(Http::class, $uri);
        self::assertEquals('https://nyholm.tech/foo', $uri->__toString());
    }
}
