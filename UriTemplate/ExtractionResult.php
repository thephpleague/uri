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

use Countable;
use Iterator;
use IteratorAggregate;
use TypeError;

use function array_key_exists;
use function array_map;
use function count;
use function is_string;

/**
 * @implements IteratorAggregate<string, ExtractedValue>
 */
final class ExtractionResult implements Countable, IteratorAggregate
{
    /** @var array<string, ExtractedValue> */
    private readonly array $variables;

    /**
     * @param iterable<string, ExtractedValue> $variables
     */
    public function __construct(iterable $variables = [])
    {
        $vars = [];
        foreach ($variables as $name => $variable) {
            is_string($name) || throw new TypeError('An extraction variable name must be a string.');
            $variable instanceof ExtractedValue || throw new TypeError('An extraction result value must be an '.ExtractedValue::class.'.');
            $vars[$name] = $variable;
        }

        $this->variables = $vars;
    }

    public function count(): int
    {
        return count($this->variables);
    }

    /**
     * @return Iterator<string, ExtractedValue>
     */
    public function getIterator(): Iterator
    {
        yield from $this->variables;
    }

    public function isEmpty(): bool
    {
        return [] === $this->variables;
    }

    public function fetch(string $name): ?ExtractedValue
    {
        return $this->variables[$name] ?? null;
    }

    public function values(): array
    {
        return array_map(static fn (ExtractedValue $val): array|string => $val->value, $this->variables);
    }

    public function value(string $name): array|string|null
    {
        return $this->fetch($name)?->value;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    public function reconcile(self $other): ?self
    {
        $result = $this->variables;

        foreach ($other as $name => $otherValue) {
            if (!array_key_exists($name, $result)) {
                $result[$name] = $otherValue;
                continue;
            }

            $value = $result[$name]->reconcile($otherValue);
            if (null === $value) {
                return null;
            }

            $result[$name] = $value;
        }

        return new self($result);
    }
}
