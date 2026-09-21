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

use function count;
use function is_array;
use function is_string;

final class ExtractedValue
{
    public readonly bool $isPartial;

    /**
     * @param array<string>|string|null $value
     *
     * @throws VariableCanNotBeExtracted
     */
    public function __construct(
        public readonly array|string|null $value,
        private readonly int $maxLength = -1,
        public readonly array $asList = [],
    ) {
        -1 === $maxLength || (is_string($value) && 0 < $maxLength) || throw VariableCanNotBeExtracted::dueTo('A prefix position can only be associated with a string value.', ExtractionErrorReason::UnsupportedOperation);
        $this->isPartial = -1 !== $maxLength;
    }

    public static function fromValue(
        array|string|null $value,
        VarSpecifier $varSpecifier,
        array $asList = [],
    ): self {
        return new self(
            $value,
            0 === $varSpecifier->position ? -1 : $varSpecifier->position,
            $asList
        );
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->maxLength === $this->maxLength
            && $value->value === $this->value
            && $value->asList === $this->asList;
    }

    public function hasKey(string|int $name): bool
    {
        return is_array($this->value) && array_key_exists($name, $this->value);
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    public function reconcile(self $other): self
    {
        if ($this->equals($other)) {
            return 1 < count($this->asList) ? new self($this->asList) : $this;
        }

        $thisValue = $this->value;
        $otherValue = $other->value;
        if ([] !== $this->asList && is_array($otherValue) && $this->asList === $otherValue) {
            return $other;
        }

        if ([] !== $other->asList && is_array($thisValue) && $other->asList === $thisValue) {
            return $this;
        }

        (!is_array($thisValue) || !is_array($otherValue)) || throw VariableCanNotBeExtracted::dueTo('The extracted lists contain different data.', ExtractionErrorReason::ListMismatch);
        (is_string($thisValue) && is_string($otherValue)) || throw VariableCanNotBeExtracted::dueTo('The extracted values have different types.', ExtractionErrorReason::TypeMismatch);
        if ($this->maxLength === $other->maxLength) {
            ($thisValue === $otherValue) || throw VariableCanNotBeExtracted::dueTo('The extracted values are different.', ExtractionErrorReason::StringMismatch);

            $value = [] === $this->asList ? $other : $this;

            return 1 < count($value->asList) ? new self($value->asList) : $value;
        }

        $result = $other;
        $resultValue = $otherValue;
        $prefixValue = $thisValue;
        if (-1 === $this->maxLength || (-1 !== $other->maxLength && $this->maxLength > $other->maxLength)) {
            $result = $this;
            $resultValue = $thisValue;
            $prefixValue = $otherValue;
        }

        return str_starts_with($resultValue, $prefixValue)
            ? $result
            : throw VariableCanNotBeExtracted::dueTo('The extracted value does not start with the other extracted value.', ExtractionErrorReason::StringMismatch);
    }
}
