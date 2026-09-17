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
use BackedEnum;
use Countable;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use LogicException;
use TypeError;
use UnitEnum;

use function array_key_exists;
use function array_map;
use function array_values;
use function count;
use function date_create_immutable_from_format;
use function date_get_last_errors;
use function enum_exists;
use function filter_var;
use function is_int;
use function is_string;
use function str_contains;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

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
     * @return array<string, array<string>|string|null>
     */
    public function variables(): array
    {
        return array_map(static fn (ExtractedValue $val): array|string|null => $val->value, $this->variables);
    }

    public function fetch(string $variableName): ?ExtractedValue
    {
        return $this->variables[$variableName] ?? null;
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

    public function string(int|string $name, ?string $default = null): ?string
    {
        $value = $this->fetch((string) $name)?->value;

        return is_string($value) ? $value : $default;
    }

    public function integer(int|string $name, ?int $default = null): ?int
    {
        $value = $this->fetch((string) $name)?->value;

        return (is_string($value) && false !== ($res = filter_var($value, FILTER_VALIDATE_INT))) ? $res : $default;
    }

    public function float(int|string $name, ?float $default = null): ?float
    {
        $value = $this->fetch((string) $name)?->value;

        return (is_string($value) && false !== ($res = filter_var($value, FILTER_VALIDATE_FLOAT))) ? $res : $default;
    }

    public function boolean(int|string $name, ?bool $default = null): ?bool
    {
        $value = $this->fetch((string) $name)?->value;
        if (!is_string($value)) {
            return $default;
        }

        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return null !== $bool ? $bool : $default;
    }

    /**
     * @param class-string<UnitEnum> $enumClass
     */
    public function enum(int|string $name, string $enumClass): ?UnitEnum
    {
        $value = $this->fetch((string) $name)?->value;
        if (!is_string($value) || !enum_exists($enumClass)) {
            return null;
        }

        $intValue = $this->integer($name);
        foreach ($enumClass::cases() as $case) {
            if ($case instanceof BackedEnum) {
                if ($case->value !== $value && $case->value !== $intValue) {
                    continue;
                }

                return $case;
            }

            if ($case->name === $value) {
                return $case;
            }
        }

        return null;
    }

    /**
     * @param non-empty-string $format
     */
    public function date(int|string $name, string $format, DateTimeZone|string|null $timezone = null): ?DateTimeImmutable
    {
        $value = $this->fetch((string) $name)?->value;
        if (!is_string($value) || str_contains($value, "\0") || '' === ($format = trim($format))) {
            return null;
        }

        if (!$timezone instanceof DateTimeZone) {
            try {
                $timezone = new DateTimeZone($timezone ?? 'UTC');
            } catch (Exception) {
                return null;
            }
        }

        $date = date_create_immutable_from_format($format, $value, $timezone);
        $errors = date_get_last_errors();

        return false !== $date
            && (false === $errors || (0 === $errors['error_count'] && 0 === $errors['warning_count'])) ? $date : null;
    }

    /**
     * @return null|string|array<string|null>
     */
    public function offsetGet(mixed $offset): null|string|array
    {
        return is_string($offset) || is_int($offset)
            ? $this->fetch((string) $offset)?->value
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
}
