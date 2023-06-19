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

final class UriBench
{
    #[Bench\OutputTimeUnit('seconds')]
    #[Bench\Assert('mode(variant.mem.peak) < 2097152'), Bench\Assert('mode(variant.time.avg) < 10000000')]
    public function benchBuildingAnUriFromUriComponents(): void
    {
        $components = [
            'scheme' => 'https',
            'host' => 'uri.thephpleague.com',
            'user' => 'php-fig',
            'pass' => 'psr7',
            'port' => 1337,
            'path' => '/5.0',
            'query' => 'q=val1&q=val2&query[3]=val3',
            'fragment' => 'foobar',
        ];

        for ($i = 0; $i < 100_000; $i++) {
            Uri::fromComponents($components);
        }
    }

    #[Bench\OutputTimeUnit('seconds')]
    #[Bench\Assert('mode(variant.mem.peak) < 2097152'), Bench\Assert('mode(variant.time.avg) < 10000000')]
    public function benchBuildingAnUriFromUriComponentsMutation(): void
    {
        for ($i = 0; $i < 100_000; $i++) {
            Uri::new()
                ->withPath('/5.0')
                ->withQuery('q=val1&q=val2&query[3]=val3')
                ->withFragment('foobar')
                ->withHost('uri.thephpleague.com')
                ->withUserInfo('user', 'pass')
                ->withScheme('https');
        }
    }
}
