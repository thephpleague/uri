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

final class Component
{
    public readonly bool $hasRegexpGroup;
    /**
     * @param list<Part> $parts
     */
    private function __construct(
        public readonly string $pattern,
        public readonly array $parts,
        public readonly string $regexp,
    ) {
        $hasRegexpGroup = false;
        foreach ($this->parts as $part) {
            if ($part->type->hasRegexpGroup()) {
                $hasRegexpGroup = true;
                break;
            }
        }

        $this->hasRegexpGroup = $hasRegexpGroup;
    }

    public static function fromAsterisk(): self
    {
        static $asterisk;
        $asterisk ??= self::fromPattern('*');

        return $asterisk;
    }

    public static function fromPattern(string $pattern): self
    {
        return new self($pattern, PathToRegexp::parse($pattern), PathToRegexp::stringToRegexp($pattern));
    }
}
