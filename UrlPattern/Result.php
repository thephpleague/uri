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
    private readonly bool $isEmpty;

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
        $this->isEmpty = $this->scheme->isEmpty()
            && $this->username->isEmpty()
            && $this->password->isEmpty()
            && $this->host->isEmpty()
            && $this->port->isEmpty()
            && $this->path->isEmpty()
            && $this->query->isEmpty()
            && $this->fragment->isEmpty();
    }

    /**
     * Tells whether the Resul contain any variable.
     */
    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    /**
     * @param array<non-empty-string, ComponentResult> $extraction
     */
    public static function tryFrom(array $extraction): ?self
    {
        foreach ($extraction as $name => $value) {
            if (!$value instanceof ComponentResult || null === ComponentName::tryFrom($name)) {
                return null;
            }
        }

        $empty = ComponentResult::empty();

        return new self(
            scheme: $extraction[ComponentName::Scheme->value] ?? $empty,
            username: $extraction[ComponentName::Username->value] ?? $empty,
            password: $extraction[ComponentName::Password->value] ?? $empty,
            host: $extraction[ComponentName::Host->value] ?? $empty,
            port: $extraction[ComponentName::Port->value] ?? $empty,
            path: $extraction[ComponentName::Path->value] ?? $empty,
            query: $extraction[ComponentName::Query->value] ?? $empty,
            fragment: $extraction[ComponentName::Fragment->value] ?? $empty,
        );
    }
}
