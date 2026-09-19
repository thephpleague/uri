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
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Stringable;
use UnitEnum;
use ValueError;

use function enum_exists;
use function filter_var;
use function is_bool;
use function is_float;
use function is_int;
use function is_iterable;
use function is_scalar;
use function is_string;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

final class TypeConverter
{
    private function __construct()
    {
    }

    public static function toString(mixed $value): ?string
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            is_scalar($value),
            $value instanceof Stringable => (string) $value,
            default => null,
        };
    }

    /**
     * @return array<string>
     */
    public static function toStrings(mixed $values, ?string $default = null): array
    {
        if (!is_iterable($values)) {
            return [];
        }

        $arr = [];
        foreach ($values as $index => $value) {
            $item = self::toString($value) ?? $default;
            if (null === $item) {
                return [];
            }

            $arr[$index] = $item;
        }

        return $arr;
    }

    public static function toInteger(mixed $value): ?int
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            is_int($value) => $value,
            !is_string($value) => null,
            default => false !== ($res = filter_var($value, FILTER_VALIDATE_INT)) ? $res : null,
        };
    }

    /**
     * @return array<int>
     */
    public static function toIntegers(mixed $values, ?int $default = null): array
    {
        if (!is_iterable($values)) {
            return [];
        }

        $arr = [];
        foreach ($values as $index => $value) {
            $item = self::toInteger($value) ?? $default;
            if (null === $item) {
                return [];
            }

            $arr[$index] = $item;
        }

        return $arr;
    }

    public static function toFloat(mixed $value): ?float
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            is_float($value) => $value,
            is_int($value) => (float) $value,
            !is_string($value) => null,
            default => false !== ($res = filter_var($value, FILTER_VALIDATE_FLOAT)) ? $res : null,
        };
    }

    /**
     * @return array<float>
     */
    public static function toFloats(mixed $values, ?float $default = null): array
    {
        if (!is_iterable($values)) {
            return [];
        }

        $arr = [];
        foreach ($values as $index => $value) {
            $item = self::toFloat($value) ?? $default;
            if (null === $item) {
                return [];
            }

            $arr[$index] = $item;
        }

        return $arr;
    }

    public static function toBoolean(mixed $value): ?bool
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            is_bool($value) => $value,
            1 === $value => true,
            0 === $value => false,
            !is_string($value) => null,
            default => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        };
    }

    /**
     * @return array<bool>
     */
    public static function toBooleans(mixed $values, ?bool $default = null): array
    {
        if (!is_iterable($values)) {
            return [];
        }

        $arr = [];
        foreach ($values as $index => $value) {
            $item = self::toBoolean($value) ?? $default;
            if (null === $item) {
                return [];
            }

            $arr[$index] = $item;
        }

        return $arr;
    }

    /**
     * @param class-string<UnitEnum> $enumClass
     */
    public static function toEnum(mixed $value, string $enumClass): ?UnitEnum
    {
        return match (true) {
            !enum_exists($enumClass) => throw new ValueError($enumClass.' does not exist or could not be found.'),
            $value instanceof $enumClass => $value,
            !is_string($value) && !is_int($value) => null,
            default => self::findEnum($value, $enumClass::cases()),
        };
    }

    /**
     * @param class-string<UnitEnum> $enumClass
     *
     * @return array<UnitEnum>
     */
    public static function toEnums(mixed $values, string $enumClass, ?UnitEnum $default = null): array
    {
        if (!is_iterable($values)) {
            return [];
        }

        enum_exists($enumClass) || throw new ValueError($enumClass.' does not exist or could not be found.');
        null === $default || $default instanceof $enumClass || throw new ValueError('The default value must be an instance of '.$enumClass.'; '.get_debug_type($default).' given.');

        $cases = $enumClass::cases();
        $arr = [];
        foreach ($values as $index => $value) {
            $item = self::findEnum($value, $cases) ?? $default;
            if (null === $item) {
                return [];
            }

            $arr[$index] = $item;
        }

        return $arr;
    }

    /**
     * @param array<UnitEnum> $cases
     */
    private static function findEnum(mixed $value, array $cases): ?UnitEnum
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $intValue = is_int($value) ? $value : self::toInteger($value);
        $value = (string) $value;

        if ($cases[0] instanceof BackedEnum) {
            foreach ($cases as $case) {
                /* @phpstan-ignore-next-line */
                if ($intValue === $case->value || $value === $case->value) {
                    return $case;
                }
            }

            return null;
        }

        foreach ($cases as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        return null;
    }

    /**
     * @param non-empty-string $format
     *
     * @throws Exception if the provided timezone is invalid
     */
    public static function toDateTimeImmutable(mixed $value, string $format, DateTimeZone|string|null $timezone = null): ?DateTimeImmutable
    {
        $format = trim($format);
        '' !== $format || throw new ValueError('The format must be a non-empty string.');
        $timezone = !$timezone instanceof DateTimeZone ? new DateTimeZone($timezone ?? 'UTC') : $timezone;

        return self::createDateTimeImmutable($value, $format, $timezone);
    }

    /**
     * @param non-empty-string $format
     *
     * @throws Exception
     *
     * @return array<DateTimeImmutable>
     */
    public static function toDateTimeImmutables(
        mixed $values,
        string $format,
        DateTimeZone|string|null $timezone = null,
        DateTimeInterface|null $default = null,
    ): array {
        if (!is_iterable($values)) {
            return [];
        }

        $format = trim($format);
        '' !== $format || throw new ValueError('The format must be a non-empty string.');
        if (!$timezone instanceof DateTimeZone) {
            $timezone = new DateTimeZone($timezone ?? 'UTC');
        }

        if (null !== $default) {
            $default = DateTimeImmutable::createFromInterface($default);
        }

        $arr = [];
        foreach ($values as $index => $value) {
            $item = self::createDateTimeImmutable($value, $format, $timezone) ?? $default;
            if (null === $item) {
                return [];
            }

            $arr[$index] = $item;
        }

        return $arr;
    }

    /**
     * @param non-empty-string $format
     */
    private static function createDateTimeImmutable(mixed $value, string $format, DateTimeZone $timezone): ?DateTimeImmutable
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (!is_string($value) || str_contains($value, "\0")) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat($format, $value, $timezone);
        $errors = DateTimeImmutable::getLastErrors();

        return false !== $date
            && (false === $errors || (0 === $errors['error_count'] && 0 === $errors['warning_count'])) ? $date : null;
    }
}
