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

use ArrayAccess;
use Countable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use League\Uri\Encoder;
use League\Uri\HostRecord;
use League\Uri\TypeConverter;
use LogicException;
use TypeError;
use UnitEnum;
use ValueError;

use function array_key_exists;
use function count;
use function is_int;
use function is_string;
use function preg_match;

/**
 * @implements ArrayAccess<array-key, string|null>
 */
final class ComponentResult implements ArrayAccess, Countable
{
    /**
     * @param array<array-key, string|null> $data
     */
    public function __construct(
        public readonly string $input,
        private readonly array $data
    ) {
    }

    public static function extract(
        string $input,
        Component $component,
        ComponentName $componentName,
        MatchMode $matchMode,
    ): ?self {
        $modifier = MatchMode::CaseInsensitive === $matchMode ? 'i' : '';
        $regexp = '~'.$component->regexp.'~'.$modifier;
        if (1 !== preg_match($regexp, $input, $matches)) {
            return null;
        }

        $data = [];
        $matchIndex = 1;
        foreach ($component->parts as $part) {
            if (PartType::Fixed === $part->type) {
                continue;
            }

            $content = $matches[$matchIndex++] ?? null;
            $data[$part->name] = ComponentName::Host === $componentName
                ? HostRecord::from($content)->toUnicode()
                : Encoder::decodeAll($content);
        }

        if ('*' === $component->pattern && [''] === $data) {
            $data = [];
        }

        return new self($component->pattern, $data);
    }

    public static function empty(): self
    {
        return new self('', []);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function isEmpty(): bool
    {
        return [] === $this->data;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->data);
    }

    /**
     * @return array<array-key, string|null>
     */
    public function variables(): array
    {
        return $this->data;
    }

    public function offsetGet(mixed $offset): null|string
    {
        return is_string($offset) || is_int($offset)
            ? ($this->data[$offset] ?? null)
            : throw new TypeError('invalid offset type, only string or integer are allowed.');
    }

    public function offsetExists(mixed $offset): bool
    {
        return (is_string($offset) || is_int($offset))
            && array_key_exists($offset, $this->data);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException(self::class.' is read-only.');
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException(self::class.' is read-only.');
    }

    public function string(int|string $name, ?string $default = null): ?string
    {
        return TypeConverter::toString($this->data[$name] ?? null) ?? $default;
    }

    public function integer(int|string $name, ?int $default = null): ?int
    {
        return TypeConverter::toInteger($this->data[$name] ?? null) ?? $default;
    }

    public function float(int|string $name, ?float $default = null): ?float
    {
        return TypeConverter::toFloat($this->data[$name] ?? null) ?? $default;
    }

    public function boolean(int|string $name, ?bool $default = null): ?bool
    {
        return TypeConverter::toBoolean($this->data[$name] ?? null) ?? $default;
    }

    /**
     * @param class-string<UnitEnum> $enumClass
     */
    public function enum(int|string $name, string $enumClass, ?UnitEnum $default = null): ?UnitEnum
    {
        null === $default || $default instanceof $enumClass || throw new ValueError('The default value must be an instance of '.$enumClass.'; '.get_debug_type($default).' given.');

        return TypeConverter::toEnum($this->data[$name] ?? null, $enumClass) ?? $default;
    }

    /**
     * @param non-empty-string $format
     *
     * @throws Exception
     */
    public function date(
        int|string $name,
        string $format,
        DateTimeZone|string $timezone = 'UTC',
        ?DateTimeInterface $default = null
    ): ?DateTimeImmutable {

        return TypeConverter::toDateTimeImmutable($this->data[$name] ?? null, $format, $timezone) ?? (
            null !== $default && !$default instanceof DateTimeImmutable
            ? DateTimeImmutable::createFromInterface($default)
            : $default
        );
    }
}
