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

/**
 * @internal The class is used to allow UriTemplate variable extraction
 */
final class ExtractionPattern
{
    public function __construct(
        public readonly string $single,
        public readonly ?string $exploded = null,
    ) {
    }
}
