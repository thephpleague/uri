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

namespace League\Uri\UrlPattern;

final class Result
{
    private const COMPONENT_NAMES = ['scheme' => 1, 'username' => 1, 'password' => 1, 'host' => 1, 'port' => 1, 'path' => 1, 'query' => 1, 'fragment' => 1];

    private function __construct(
        public readonly ComponentResult $scheme,
        public readonly ComponentResult $username,
        public readonly ComponentResult $password,
        public readonly ComponentResult $host,
        public readonly ComponentResult $port,
        public readonly ComponentResult $path,
        public readonly ComponentResult $query,
        public readonly ComponentResult $fragment,
    ) {

    }

    /**
     * @param array<'scheme'|'username'|'password'|'host'|'port'|'path'|'query'|'fragment', ComponentResult> $extraction
     */
    public static function tryFrom(array $extraction): ?self
    {
        foreach ($extraction as $name => $value) {
            if (!$value instanceof ComponentResult || !isset(self::COMPONENT_NAMES[$name])) {
                return null;
            }
        }

        return new self(
            scheme: $extraction['scheme'] ?? ComponentResult::empty(),
            username: $extraction['username'] ?? ComponentResult::empty(),
            password: $extraction['password'] ?? ComponentResult::empty(),
            host: $extraction['host'] ?? ComponentResult::empty(),
            port: $extraction['port'] ?? ComponentResult::empty(),
            path: $extraction['path'] ?? ComponentResult::empty(),
            query: $extraction['query'] ?? ComponentResult::empty(),
            fragment: $extraction['fragment'] ?? ComponentResult::empty(),
        );
    }
}
