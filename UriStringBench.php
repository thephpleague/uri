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

use PhpBench\Attributes as Bench;

final class UriStringBench
{
    #[Bench\OutputTimeUnit('seconds')]
    #[Bench\Assert('mode(variant.mem.peak) < 2097152'), Bench\Assert('mode(variant.time.avg) < 10000000')]
    public function benchParsingARegularUri(): void
    {
        $uri = 'https://uri.thephpleague.com:1337/5.0?query=value1&query=value2#foobar';

        for ($i = 0; $i < 100_000; $i++) {
            UriString::parse($uri);
        }
    }

    #[Bench\OutputTimeUnit('seconds')]
    #[Bench\Assert('mode(variant.mem.peak) < 2097152'), Bench\Assert('mode(variant.time.avg) < 10000000')]
    public function benchBuildingARegularUri(): void
    {
        $components = [
            'scheme' => 'https',
            'user' => 'phantom',
            'pass' => 'menace',
            'host' => 'uri.thephpleague.com',
            'port' => 1337,
            'path' => '/5.0',
            'query' => 'query=value1&query=value2',
            'fragment' => 'foobar',
        ];

        for ($i = 0; $i < 100_000; $i++) {
            UriString::build($components);
        }
    }
}
