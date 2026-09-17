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
            $result[$name] = !array_key_exists($name, $result)
                ? $value
                : $result[$name]->reconcile($value) ?? throw VariableCanNotBeExtracted::dueTo(
                    'The extracted values for variable "'.$name.'" could not be reconciled.',
                    ExtractionErrorReason::ReconciliationFailed
                );
        }

        return self::success($result);
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
