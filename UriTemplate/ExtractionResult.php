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
    /** @var array<string> */
    public readonly array $missingVariables;

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
        $missingVariables = [];
        foreach ($vars as $key => $v) {
            if (null === $v->value) {
                $missingVariables[] = $key;
            }
        }

        $this->missingVariables = $missingVariables;
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

    public function fetch(string $variableName): ?ExtractedValue
    {
        return $this->variables[$variableName] ?? null;
    }

    public function variables(): array
    {
        return array_map(static fn (ExtractedValue $val): array|string|null => $val->value, $this->variables);
    }

    public function value(string $variableName): array|string|null
    {
        return $this->fetch($variableName)?->value;
    }

    public function has(string $variableName): bool
    {
        return array_key_exists($variableName, $this->variables);
    }

    public function reconcile(self $other): self
    {
        $result = $this->variables;
        foreach ($other as $name => $otherValue) {
            if (!array_key_exists($name, $result)) {
                $result[$name] = $otherValue;
                continue;
            }

            $value = $result[$name]->reconcile($otherValue)
                ?? throw new VariableCanNotBeExtracted('The extracted values for variable "'.$name.'" could not be reconciled.');

            $result[$name] = $value;
        }

        return new self($result);
    }
}
