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

use ArrayAccess;
use Countable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use League\Uri\TypeConverter;
use LogicException;
use TypeError;
use UnitEnum;

use function array_key_exists;
use function array_map;
use function array_values;
use function count;
use function is_int;
use function is_string;

/**
 * @implements ArrayAccess<string, null|string|array<string|null>>
 */
final class ExtractionResult implements ArrayAccess, Countable
{
    /**
     * @param array<string, ExtractedValue> $variables
     * @param list<string> $names
     * @param list<string> $missingNames
     * @param list<ExtractionErrorReason> $reasons
     */
    private function __construct(
        private readonly array $variables,
        private readonly array $names,
        private readonly array $missingNames,
        private readonly array $reasons,
    ) {
    }

    public static function failure(VariableCanNotBeExtracted $exception, Template $template): self
    {
        return new self(
            [],
            $template->variableNames,
            $exception->getMissingNames(),
            $exception->getReasons(),
        );
    }

    public static function success(iterable $variables = []): self
    {
        $vars = [];
        $names = [];
        $missing = [];
        foreach ($variables as $name => $variable) {
            is_string($name) || is_int($name) || throw new TypeError('An extraction variable name must be a string.');
            $variable instanceof ExtractedValue || throw new TypeError('An extraction result value must be an '.ExtractedValue::class.'.');
            $name = (string) $name;
            $vars[$name] = $variable;
            $names[$name] = $name;
            if (null === $variable->value) {
                $missing[$name] = $name;
            }
        }

        return new self($vars, array_values($names), array_values($missing), []);
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    public function reconcile(self $other): self
    {
        $result = $this->variables;
        foreach ($other->variables as $name => $value) {
            if (!array_key_exists($name, $result)) {
                $result[$name] = $value;
                continue;
            }

            try {
                $result[$name] = $result[$name]->reconcile($value);
            } catch (VariableCanNotBeExtracted $exception) {
                throw VariableCanNotBeExtracted::dueTo(
                    'The extracted values for variable "'.$name.'" could not be reconciled; '.$exception->getMessage(),
                    ...$exception->getReasons(),
                );
            }
        }

        return self::success($result);
    }

    /**
     * Returns true if the extraction is successful.
     */
    public function isSuccessful(): bool
    {
        return [] === $this->reasons;
    }

    /**
     * @return list<ExtractionErrorReason>
     */
    public function reasons(): array
    {
        return $this->reasons;
    }

    /**
     * Returns the list of all variable names.
     *
     * @return list<string>
     */
    public function names(): array
    {
        return $this->names;
    }

    /**
     * Returns the list of variable names missing from the extraction.
     *
     * @return list<string>
     */
    public function missingNames(): array
    {
        return $this->missingNames;
    }

    /**
     * Tells whether some variables are attached to the result.
     */
    public function isEmpty(): bool
    {
        return [] === $this->variables;
    }

    /**
     * Returns the number of found variables.
     */
    public function count(): int
    {
        return count($this->variables);
    }

    /**
     * @return null|string|array<string|null>
     */
    public function offsetGet(mixed $offset): null|string|array
    {
        return is_string($offset) || is_int($offset)
            ? $this->fetch($offset)?->value
            : throw new TypeError('offset must be a string.');
    }

    public function offsetExists(mixed $offset): bool
    {
        return (is_string($offset) || is_int($offset))
            && array_key_exists((string) $offset, $this->variables);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException(self::class.' is read-only.');
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException(self::class.' is read-only.');
    }

    /**
     * @return array<string, array<string>|string|null>
     */
    public function variables(): array
    {
        return array_map(static fn (ExtractedValue $val): array|string|null => $val->value, $this->variables);
    }

    public function fetch(string|int $variableName): ?ExtractedValue
    {
        return $this->variables[$variableName] ?? null;
    }

    public function string(int|string $name, ?string $default = null): ?string
    {
        return TypeConverter::toString($this->fetch($name)?->value) ?? $default;
    }

    public function strings(int|string $name, ?string $default = null): array
    {
        return TypeConverter::toStrings($this->fetch($name)?->value, $default);
    }

    public function integer(int|string $name, ?int $default = null): ?int
    {
        return TypeConverter::toInteger($this->fetch($name)?->value) ?? $default;
    }

    /**
     * @return array<int>
     */
    public function integers(int|string $name, ?int $default = null): array
    {
        return TypeConverter::toIntegers($this->fetch($name)?->value, $default);
    }

    public function float(int|string $name, ?float $default = null): ?float
    {
        return TypeConverter::toFloat($this->fetch($name)?->value) ?? $default;
    }

    /**
     * @return array<float>
     */
    public function floats(int|string $name, ?float $default = null): array
    {
        return TypeConverter::toFloats($this->fetch($name)?->value, $default);
    }

    public function boolean(int|string $name, ?bool $default = null): ?bool
    {
        return TypeConverter::toBoolean($this->fetch($name)?->value) ?? $default;
    }

    /**
     * @return array<bool>
     */
    public function booleans(int|string $name, ?bool $default = null): array
    {
        return TypeConverter::toBooleans($this->fetch($name)?->value, $default);
    }

    /**
     * @param class-string<UnitEnum> $enumClass
     */
    public function enum(int|string $name, string $enumClass): ?UnitEnum
    {
        return TypeConverter::toEnum($this->fetch($name)?->value, $enumClass);
    }

    /**
     * @param class-string<UnitEnum> $enumClass
     *
     * @return array<UnitEnum>
     */
    public function enums(int|string $name, string $enumClass, ?UnitEnum $default = null): array
    {
        return TypeConverter::toEnums($this->fetch($name)?->value, $enumClass, $default);
    }

    /**
     * @param non-empty-string $format
     *
     * @throws Exception
     */
    public function date(int|string $name, string $format, DateTimeZone|string|null $timezone = null): ?DateTimeImmutable
    {
        return TypeConverter::toDateTimeImmutable($this->fetch($name)?->value, $format, $timezone);
    }

    /**
     * @param non-empty-string $format
     *
     * @throws Exception
     *
     * @return array<DateTimeImmutable>
     */
    public function dates(int|string $name, string $format, DateTimeZone|string|null $timezone = null, ?DateTimeInterface $default = null): array
    {
        return TypeConverter::toDateTimeImmutables($this->fetch($name)?->value, $format, $timezone, $default);
    }
}
