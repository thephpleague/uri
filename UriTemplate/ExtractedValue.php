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

use function is_string;

final class ExtractedValue
{
    public readonly bool $isPartial;

    /**
     * @param string|array<string> $value
     *
     * @throws VariableCanNotBeExtracted
     */
    public function __construct(
        public readonly string|array $value,
        private readonly int $maxLength = -1,
    ) {
        -1 === $maxLength || (is_string($value) && 0 < $maxLength) || throw new VariableCanNotBeExtracted('A prefix position can only be associated with a string value.');
        $this->isPartial = -1 !== $maxLength;
    }

    public static function fromValue(array|string $value, VarSpecifier $varSpecifier): self
    {
        return new self(
            $value,
            0 === $varSpecifier->position ? -1 : $varSpecifier->position,
        );
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->maxLength === $this->maxLength
            && $value->value === $this->value;
    }

    public function reconcile(self $other): ?self
    {
        if ($this->maxLength === $other->maxLength) {
            return $this->value === $other->value ? $this : null;
        }

        $result = -1 === $this->maxLength || (-1 !== $other->maxLength && $this->maxLength > $other->maxLength) ? $this : $other;
        $prefix = $result === $this ? $other : $this;

        return match (true) {
            !is_string($prefix->value),
            !is_string($result->value),
            !str_starts_with($result->value, $prefix->value) => null,
            default => $result,
        };
    }
}
