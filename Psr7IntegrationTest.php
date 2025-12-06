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

use Http\Psr7Test\UriIntegrationTest;

final class Psr7IntegrationTest extends UriIntegrationTest
{
    public function createUri($uri)
    {
        return (new HttpFactory())->createUri($uri);
    }

    public function testSpecialCharsInUserInfo(): void
    {
        $uri = $this->createUri('http://example.com')->withUserInfo('foo@bar.com', 'pass#word');
        self::assertSame('foo%40bar.com:pass%23word', $uri->getUserInfo());
    }

    public function testAlreadyEncodedUserInfo(): void
    {
        $uri = $this->createUri('http://example.com')->withUserInfo('foo%40bar.com', 'pass%23word');
        self::assertSame('foo%40bar.com:pass%23word', $uri->getUserInfo());
    }
}
