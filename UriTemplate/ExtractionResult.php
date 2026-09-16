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
use LogicException;
use TypeError;

use function array_key_exists;
use function array_keys;
use function array_map;
use function count;
use function is_string;

/**
 * @implements ArrayAccess<string, null|string|array<string|null>>
 */
final class ExtractionResult implements ArrayAccess, Countable
{
    /**
     * @param array<string, ExtractedValue> $variables
     * @param list<string> $missingVariables
     * @param list<ExtractionErrorReason> $reasons
     */
    private function __construct(
        private readonly array $variables,
        private readonly array $missingVariables,
        private readonly array $reasons,
    ) {
    }

    public static function failure(VariableCanNotBeExtracted $exception): self
    {
        return new self(
            [],
            $exception->getMissingVariables(),
            $exception->getReasons(),
        );
    }

    public static function success(iterable $variables = []): self
    {
        $vars = [];
        foreach ($variables as $name => $variable) {
            is_string($name) || throw new TypeError('An extraction variable name must be a string.');
            $variable instanceof ExtractedValue || throw new TypeError('An extraction result value must be an '.ExtractedValue::class.'.');
            $vars[$name] = $variable;
        }

        $missingVariables = [];
        foreach ($vars as $key => $v) {
            if (null === $v->value) {
                $missingVariables[] = $key;
            }
        }

        return new self($vars, $missingVariables, []);
    }

    /**
     * Returns the number of found variables.
     */
    public function count(): int
    {
        return count($this->variables);
    }

    /**
     * Tells whether some variables are attached to the result.
     */
    public function isEmpty(): bool
    {
        return [] === $this->variables;
    }

    /**
     * Returns true if the extraction is successful.
     */
    public function isSuccessful(): bool
    {
        return [] === $this->reasons;
    }

    /**
     * @return list<string>
     */
    public function variableNames(): array
    {
        return array_keys($this->variables);
    }

    /**
     * @return list<string>
     */
    public function missingVariables(): array
    {
        return $this->missingVariables;
    }

    public function has(string $variableName): bool
    {
        return array_key_exists($variableName, $this->variables);
    }

    public function fetch(string $variableName): ?ExtractedValue
    {
        return $this->variables[$variableName] ?? null;
    }

    public function value(string $variableName): array|string|null
    {
        return $this->fetch($variableName)?->value;
    }

    /**
     * @return array<string, array<string>|string|null>
     */
    public function variables(): array
    {
        return array_map(static fn (ExtractedValue $val): array|string|null => $val->value, $this->variables);
    }

    /**
     * @return list<ExtractionErrorReason>
     */
    public function reasons(): array
    {
        return $this->reasons;
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    public function reconcile(self $other): self
    {
        $result = $this->variables;
        foreach ($other->variables as $name => $otherValue) {
            if (!array_key_exists($name, $result)) {
                $result[$name] = $otherValue;
                continue;
            }

            $value = $result[$name]->reconcile($otherValue)
                ?? throw new VariableCanNotBeExtracted('The extracted values for variable "'.$name.'" could not be reconciled.', [ExtractionErrorReason::ReconciliationFailed]);

            $result[$name] = $value;
        }

        return self::success($result);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->has($offset);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException(self::class . ' is read-only.');
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException(self::class . ' is read-only.');
    }

    /**
     * @return null|string|array<string|null>
     */
    public function offsetGet(mixed $offset): null|string|array
    {
        return is_string($offset) ? $this->value($offset) : throw new TypeError('offset must be a string.');
    }
}
