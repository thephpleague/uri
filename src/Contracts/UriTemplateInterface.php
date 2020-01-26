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

namespace League\Uri\Contracts;

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
     * The distinct variable names used in the template.
     *
     * @return string[]
     */
    public function getVariableNames(): array;

    /**
     * The default value used to expand the template.
     *
     * @return array<string,string|array>
     */
    public function getDefaultVariables(): array;

    /**
     * @throws UriException if the expansion can not be done.
     */
    public function expand(array $variables = []): UriInterface;

    /**
     * @param object|string $template a string or an object with the __toString method
     *
     * @throw UriException if the template syntax is invalid
     */
    public function withTemplate($template): self;

    /**
     * @param array<string,string|array> $defaultVariables
     */
    public function withDefaultVariables(array $defaultVariables): self;
}