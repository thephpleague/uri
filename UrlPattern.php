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
use League\Uri\Exceptions\SyntaxError;
use League\Uri\UrlPattern\Component;
use League\Uri\UrlPattern\ComponentResult;
use League\Uri\UrlPattern\MatchMode;
use League\Uri\UrlPattern\PartType;
use League\Uri\UrlPattern\Result;
use Stringable;
use TypeError;
use Uri\Rfc3986\Uri as Rfc3986Uri;
use Uri\WhatWg\Url as WhatWgUrl;
use ValueError;

use function array_key_exists;
use function array_map;
use function get_debug_type;
use function in_array;
use function preg_match;

/**
 * @property-read ?string $scheme
 * @property-read ?string $username
 * @property-read ?string $password
 * @property-read ?string $host
 * @property-read ?string $port
 * @property-read ?string $path
 * @property-read ?string $query
 * @property-read ?string $fragment
 */
final class UrlPattern
{
    private const COMPONENT_NAMES = ['scheme', 'username', 'password', 'host', 'port', 'path', 'query', 'fragment'];
    /** @var array<'scheme'|'username'|'password'|'host'|'port'|'path'|'query'|'fragment', Component> $components */
    private readonly array $components;
    public readonly MatchMode $matchMode;
    public readonly bool $hasRegexpGroup;
    public readonly bool $hasVariable;

    /**
     * @param array<'scheme'|'username'|'password'|'host'|'port'|'path'|'query'|'fragment', Component> $patternComponents
     */
    public function __construct(array $patternComponents, MatchMode $matchMode = MatchMode::CaseSensitive)
    {
        $hasRegexpGroup = false;
        $hasVariable = false;
        $components = [];
        foreach (self::COMPONENT_NAMES as $name) {
            if (! array_key_exists($name, $patternComponents)) {
                $components[$name] = Component::fromAsterisk();
                continue;
            }

            $component = $patternComponents[$name];
            /* @phpstan-ignore-next-line */
            $component instanceof Component || throw new TypeError('the component must be a "'.Component::class.'"; '.get_debug_type($component).' given.');
            $components[$name] = $component;
            $hasRegexpGroup = $hasRegexpGroup || $component->hasRegexpGroup;
            $hasVariable = $hasVariable || $component->hasVariable;
        }

        $this->components = $components;
        $this->matchMode = $matchMode;
        $this->hasRegexpGroup = $hasRegexpGroup;
        $this->hasVariable = $hasVariable;
    }

    public static function from(
        Stringable|string $pattern,
        Rfc3986Uri|WhatWgUrl|BackedEnum|Stringable|string|null $baseUrl = null,
        MatchMode $matchMode = MatchMode::CaseSensitive
    ): self {
        return UrlPatternBuilder::from($pattern)
            ->when(
                MatchMode::CaseInsensitive === $matchMode,
                fn (UrlPatternBuilder $builder) => $builder->ignoreCase(),
                fn (UrlPatternBuilder $builder) => $builder->preserveCase()
            )
            ->build($baseUrl);
    }

    public function __get(string $name): ?string
    {
        in_array($name, self::COMPONENT_NAMES, true) || throw new ValueError('the property named "'.$name.'" does not exist.');

        return array_key_exists($name, $this->components) ? $this->components[$name]->pattern : null;
    }

    public function match(Rfc3986Uri|WhatWgUrl|BackedEnum|Stringable|string $input): bool
    {
        return $this->extract($input) instanceof Result;
    }

    /**
     * @throws SyntaxError
     */
    public function extract(Rfc3986Uri|WhatWgUrl|BackedEnum|Stringable|string $input): ?Result
    {
        $uriString = match (true) {
            $input instanceof Rfc3986Uri => $input->toRawString(),
            $input instanceof WhatWgUrl => $input->toAsciiString(),
            default => $input,
        };

        $components = array_map(static fn (string|int|null $value): string => (string) $value, UriString::parse($uriString));
        $components['username'] = $components['user'];
        $components['password'] = $components['pass'];

        $result = [];
        foreach ($this->components as $name => $component) {
            $found = $this->extractComponent($component, $components[$name], $name);
            if (null === $found) {
                return null;
            }

            $result[$name] = $found;
        }

        return Result::tryFrom($result);
    }

    private function extractComponent(Component $component, string $source, string $name): ?ComponentResult
    {
        $matches = [];
        $modifier = MatchMode::CaseInsensitive === $this->matchMode ? 'i' : '';
        $regexp = '~'.$component->regexp.'~'.$modifier;
        if (1 !== preg_match($regexp, $source, $matches)) {
            return null;
        }

        $data = [];
        $matchIndex = 1;
        foreach ($component->parts as $part) {
            if (PartType::Fixed === $part->type) {
                continue;
            }

            $content = $matches[$matchIndex++] ?? null;
            $data[$part->name] = 'host' === $name
                ? HostRecord::from($content)->toUnicode()
                : Encoder::decodeAll($content);
        }

        return new ComponentResult($component->pattern, $data);
    }

    /**
     * @return array<'scheme'|'username'|'password'|'host'|'port'|'path'|'query'|'fragment', string>
     */
    public function __debugInfo(): array
    {
        return array_map(static fn (Component $component): string => $component->pattern, $this->components);
    }
}
