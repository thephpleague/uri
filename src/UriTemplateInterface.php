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

use League\Uri\Contracts\UriInterface;
use League\Uri\Exceptions\SyntaxError;

/**
 * Expands URI templates.
 *
 * @link http://tools.ietf.org/html/rfc6570
 *
 * Based on GuzzleHttp\UriTemplate class which is removed from Guzzle7.
 */
interface UriTemplateInterface
{
    /**
     * The template string.
     */
    public function getTemplate(): string;

    /**
     * The default value used to expand the template.
     *
     * @return array<string,string|array>
     */
    public function getDefaultVariables(): array;

    /**
     * @throws SyntaxError if the variables contains nested array values
     */
    public function expand(array $variables = []): UriInterface;
}
