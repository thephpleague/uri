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

use PhpBench\Attributes as Bench;

final class TemplateBench
{
    #[Bench\OutputTimeUnit('seconds')]
    #[Bench\Assert('mode(variant.mem.peak) < 2097152'), Bench\Assert('mode(variant.time.avg) < 10000000')]
    public function benchBuildingAnUriStringFromATemplateAndAVariableBag(): void
    {
        $template = 'https://uri.thephpleague.com/{foo}{?query,limit}';
        $uriTemplate = Template::new($template);
        $data = new VariableBag([
            'foo' => 'foo',
            'query' => ['foo', 'bar', 'baz'],
            'limit' => 10,
        ]);

        for ($i = 0; $i < 100_000; $i++) {
            $uriTemplate->expand($data);
        }
    }
}
