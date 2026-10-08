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

use BackedEnum;
use League\Uri\Contracts\Conditionable;
use League\Uri\UrlPattern\Component;
use League\Uri\UrlPattern\ComponentName;
use League\Uri\UrlPattern\MatchMode;
use League\Uri\UrlPattern\Parser;
use Stringable;
use Uri\Rfc3986\Uri as Rfc3986Uri;
use Uri\WhatWg\Url as WhatWgUrl;

use function array_filter;
use function is_bool;
use function strrpos;
use function substr;

final class UrlPatternBuilder implements Conditionable
{
    private ?Component $scheme = null;
    private ?Component $username = null;
    private ?Component $password = null;
    private ?Component $host = null;
    private ?Component $port = null;
    private ?Component $path = null;
    private ?Component $query = null;
    private ?Component $fragment = null;
    private MatchMode $matchMode = MatchMode::CaseSensitive;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->scheme = null;
        $this->username = null;
        $this->password = null;
        $this->host = null;
        $this->port = null;
        $this->path = null;
        $this->query = null;
        $this->fragment = null;
        $this->matchMode = MatchMode::CaseSensitive;
    }

    public static function from(Stringable|string $pattern, MatchMode $matchMode = MatchMode::CaseSensitive): self
    {
        $components = (new Parser((string) $pattern))->components();

        return (new self())
            ->scheme($components[ComponentName::Scheme->value] ?? null)
            ->username($components[ComponentName::Username->value] ?? null)
            ->password($components[ComponentName::Password->value] ?? null)
            ->host($components[ComponentName::Host->value] ?? null)
            ->port($components[ComponentName::Port->value] ?? null)
            ->path($components[ComponentName::Path->value] ?? null)
            ->query($components[ComponentName::Query->value] ?? null)
            ->fragment($components[ComponentName::Fragment->value] ?? null)
            ->when(
                MatchMode::CaseInsensitive == $matchMode,
                static fn (self $builder): self => $builder->ignoreCase(),
                static fn (self $builder): self => $builder->preserveCase(),
            );
    }

    public function build(Rfc3986Uri|WhatWgUrl|BackedEnum|Stringable|string|null $baseUrl = null): UrlPattern
    {
        $components = self::applyBaseUrl([
            ComponentName::Scheme->value => $this->scheme ?? null,
            ComponentName::Username->value => $this->username ?? null,
            ComponentName::Password->value => $this->password ?? null,
            ComponentName::Host->value => $this->host ?? null,
            ComponentName::Port->value => $this->port ?? null,
            ComponentName::Path->value => $this->path ?? null,
            ComponentName::Query->value => $this->query ?? null,
            ComponentName::Fragment->value => $this->fragment ?? null,
        ], $baseUrl);

        return new UrlPattern(
            array_filter($components, static fn (?Component $component): bool => $component instanceof Component),
            $this->matchMode
        );
    }

    public function when(callable|bool $condition, callable $onSuccess, ?callable $onFail = null): static
    {
        if (!is_bool($condition)) {
            $condition = $condition($this);
        }

        return match (true) {
            $condition => $onSuccess($this),
            null !== $onFail => $onFail($this),
            default => $this,
        } ?? $this;
    }

    /**
     * @param array $components
     * @param Rfc3986Uri|WhatWgUrl|BackedEnum|Stringable|string|null $baseUrl
     *
     * @return array<non-empty-string, Component>
     */
    private static function applyBaseUrl(array $components, Rfc3986Uri|WhatWgUrl|BackedEnum|Stringable|string|null $baseUrl): array
    {
        if (null === $baseUrl) {
            return $components;
        }

        $baseComponents = UriString::parse(match (true) {
            $baseUrl instanceof Rfc3986Uri => $baseUrl->toRawString(),
            $baseUrl instanceof WhatWgUrl => $baseUrl->toAsciiString(),
            default => $baseUrl,
        });
        $hasScheme = isset($components[ComponentName::Scheme->value]);
        $hasHost = isset($components[ComponentName::Host->value]);
        $hasPort = isset($components[ComponentName::Port->value]);

        if (! $hasScheme) {
            $components[ComponentName::Scheme->value] = null !== $baseComponents[ComponentName::Scheme->value]
                ? Component::fromPattern($baseComponents[ComponentName::Scheme->value])
                : Component::fromAsterisk();
        }

        if (! $hasScheme && ! $hasHost) {
            $components[ComponentName::Host->value] = null !== $baseComponents[ComponentName::Host->value]
                ? Component::fromPattern($baseComponents[ComponentName::Host->value])
                : Component::fromAsterisk();
        }

        if (! $hasScheme && ! $hasHost && ! $hasPort) {
            $components[ComponentName::Port->value] = null !== $baseComponents[ComponentName::Port->value]
                ? Component::fromPattern((string) $baseComponents[ComponentName::Port->value])
                : Component::fromAsterisk();
        }

        $components[ComponentName::Path->value] = match (true) {
            !isset($components[ComponentName::Path->value]) => null !== $baseComponents[ComponentName::Path->value] ? Component::fromPattern($baseComponents[ComponentName::Path->value]) : Component::fromAsterisk(),
            default => self::resolvePathname($components[ComponentName::Path->value], $baseComponents[ComponentName::Path->value]),
        };

        return $components;
    }

    private static function resolvePathname(Component $pathname, string $basePathname): Component
    {
        if (str_starts_with($pathname->pattern, '/')) {
            return $pathname;
        }

        $slash = strrpos($basePathname, '/');

        $pattern = false === $slash
            ? $pathname->pattern
            : substr($basePathname, 0, $slash + 1).$pathname->pattern;

        return Component::fromPattern($pattern);
    }

    private static function filter(Component|Stringable|string|null $component): ?Component
    {
        return match (true) {
            $component instanceof Component => $component,
            null === $component => null,
            default => Component::fromPattern((string) $component),
        };
    }

    public function scheme(Component|Stringable|string|null $scheme): self
    {
        $this->scheme = self::filter($scheme);

        return $this;
    }

    public function username(Component|Stringable|string|null $username): self
    {
        $this->username = self::filter($username);

        return $this;
    }

    public function password(Component|Stringable|string|null $password): self
    {
        $this->password = self::filter($password);

        return $this;
    }

    public function host(Component|Stringable|string|null $host): self
    {
        $this->host = self::filter($host);

        return $this;
    }

    public function port(Component|Stringable|string|null $port): self
    {
        $this->path = self::filter($port);

        return $this;
    }

    public function path(Component|Stringable|string|null $path): self
    {
        $this->path = self::filter($path);

        return $this;
    }

    public function query(Component|Stringable|string|null $query): self
    {
        $this->query = self::filter($query);

        return $this;
    }

    public function fragment(Component|Stringable|string|null $fragment): self
    {
        $this->fragment = self::filter($fragment);

        return $this;
    }

    public function ignoreCase(): self
    {
        $this->matchMode = MatchMode::CaseInsensitive;

        return $this;
    }

    public function preserveCase(): self
    {
        $this->matchMode = MatchMode::CaseSensitive;

        return $this;
    }
}
