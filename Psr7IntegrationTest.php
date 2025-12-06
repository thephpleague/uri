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
}
