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

use Deprecated;
use Iterator;
use IteratorAggregate;
use League\Uri\Exceptions\SyntaxError;
use Stringable;

use function array_filter;
use function array_map;
use function array_slice;
use function array_unique;
use function array_values;
use function count;
use function explode;
use function implode;
use function is_string;
use function ksort;

/**
 * @internal The class exposes the internal representation of an Expression and its usage
 * @link https://www.rfc-editor.org/rfc/rfc6570#section-2.2
 * @implements IteratorAggregate<VarSpecifier>
 */
final class Expression implements IteratorAggregate
{
    /** @var array<VarSpecifier> */
    private readonly array $varSpecifiers;
    /** @var array<string> */
    public readonly array $variableNames;
    public readonly string $value;

    private function __construct(public readonly Operator $operator, VarSpecifier ...$varSpecifiers)
    {
        $this->varSpecifiers = $varSpecifiers;
        $this->variableNames = array_unique(
            array_map(
                static fn (VarSpecifier $varSpecifier): string => $varSpecifier->name,
                $varSpecifiers
            )
        );
        $this->value = '{'.$operator->value.implode(',', array_map(
            static fn (VarSpecifier $varSpecifier): string => $varSpecifier->toString(),
            $varSpecifiers
        )).'}';
    }

    /**
     * @throws SyntaxError if the expression is invalid
     */
    public static function new(Stringable|string $expression): self
    {
        $parts = Operator::parseExpression($expression);

        return new Expression($parts['operator'], ...array_map(
            static fn (string $varSpec): VarSpecifier => VarSpecifier::new($varSpec),
            explode(',', $parts['variables'])
        ));
    }

    /**
     * @return Iterator<VarSpecifier>
     */
    public function getIterator(): Iterator
    {
        yield from $this->varSpecifiers;
    }

    public function expand(VariableBag $variables): string
    {
        $expanded = implode(
            $this->operator->separator(),
            array_filter(
                array_map(
                    fn (VarSpecifier $varSpecifier): string => $this->operator->expand($varSpecifier, $variables),
                    $this->varSpecifiers
                ),
                static fn ($value): bool => '' !== $value
            )
        );

        return match ('') {
            $expanded => '',
            default => $this->operator->first().$expanded,
        };
    }

    /**
     * Extracts variables from a value according to this expression.
     *
     * Named expressions match variables by name, query expressions match query
     * parameters by name regardless of their order, while positional expressions
     * assign values according to the order of their variable specifiers.
     *
     * @throws VariableCanNotBeExtracted If the value cannot be extracted according
     *                                   to the expression.
     */
    public function extract(string $value): ExtractionResult
    {
        return match (true) {
            $this->operator->isQuery() => $this->extractQueryValues($value),
            $this->operator->isNamed() => $this->extractNamedValues($value),
            default => $this->extractPositionalValues($value),
        };
    }

    /**
     * Extracts variables from a named query expression value.
     *
     * Query parameters are matched by name regardless of their order. Missing
     * variables are represented by null values, while exploded variables may
     * consume multiple query parameters.
     *
     * @throws VariableCanNotBeExtracted If the query value cannot be matched
     *                                   to the variable specifiers.
     */
    private function extractQueryValues(string $value): ExtractionResult
    {
        /** @var non-empty-string $separator */
        $separator = $this->operator->separator();
        $components = '' === $value ? [] : explode($separator, $value);
        $matched = $this->matchQueryValues($components);

        null !== $matched || throw new VariableCanNotBeExtracted('The value "'.$value.'" cannot be extracted from the expression "'.$this->value.'".', [ExtractionErrorReason::MalformedValue]);

        $variables = [];
        foreach ($this->varSpecifiers as $offset => $varSpecifier) {
            $variables[$varSpecifier->name] = $this->operator->extract($varSpecifier, $matched[$offset])->fetch($varSpecifier->name);
        }

        return ExtractionResult::success($variables);
    }

    /**
     * Matches named query components against the variable specifiers.
     *
     * Query components are matched by variable name rather than by position,
     * because query parameter order has no semantic significance. Non-exploded
     * variables may occur at most once, while exploded variables may consume
     * multiple components.
     *
     * @param list<string> $components The query components to match.
     *
     * @return list<string|null>|null One matched value per variable specifier,
     *                                with `null` for an absent variable, or
     *                                `null` if the components cannot be matched.
     */
    private function matchQueryValues(array $components): ?array
    {
        /** @var array<string, list<string>> $componentsByName */
        $componentsByName = [];

        foreach ($components as $component) {
            [$name] = explode('=', $component, 2);
            $componentsByName[$name] ??= [];
            $componentsByName[$name][] = $component;
        }

        $explodedSpecifiers = [];
        foreach ($this->varSpecifiers as $offset => $varSpecifier) {
            if ('*' === $varSpecifier->modifier) {
                $explodedSpecifiers[$offset] = $varSpecifier;
            }
        }

        $matched = [];
        foreach ($this->varSpecifiers as $offset => $varSpecifier) {
            $name = $varSpecifier->name;
            if (!isset($componentsByName[$name])) {
                $matched[$offset] = null;

                continue;
            }

            $occurrences = $componentsByName[$name];
            if ('*' !== $varSpecifier->modifier && 1 !== count($occurrences)) {
                return null;
            }

            $matched[$offset] = implode($this->operator->separator(), $occurrences);

            unset($componentsByName[$name]);
        }

        if ([] === $componentsByName) {
            ksort($matched);

            return array_values($matched);
        }

        /*
         * Remaining components have names that are not explicitly declared.
         * They can only be consumed by an exploded variable.
         */
        if (1 !== count($explodedSpecifiers)) {
            return null;
        }

        $explodedOffset = array_key_first($explodedSpecifiers);
        $remaining = [];
        foreach ($componentsByName as $occurrences) {
            foreach ($occurrences as $component) {
                $remaining[] = $component;
            }
        }

        $matched[$explodedOffset] = implode($this->operator->separator(), $remaining);
        ksort($matched);

        return array_values($matched);
    }

    /**
     * Extracts variables from a named expression value.
     *
     * Each named variable is matched against the components of the value.
     * Non-exploded variables consume at most one component, while exploded
     * variables may consume multiple components and are matched with backtracking
     * to preserve the variables that follow them.
     *
     * An empty value represents an expression for which none of the named
     * variables is present.
     *
     * @throws VariableCanNotBeExtracted If the value cannot be matched to the
     *                                   variable specifiers or a value exceeds
     *                                   its variable's prefix length.
     */
    private function extractNamedValues(string $value): ExtractionResult
    {
        /** @var non-empty-string $separator */
        $separator = $this->operator->separator();
        $components = '' === $value ? [] : explode($separator, $value);
        $matched = $this->matchNamedValues($components, 0, 0);

        null !== $matched || throw new VariableCanNotBeExtracted('The value "'.$value.'" cannot be extracted from the expression "'.$this->value.'".', [ExtractionErrorReason::MalformedValue]);

        $variables = [];
        foreach ($this->varSpecifiers as $offset => $varSpecifier) {
            $extracted = $this->operator->extract($varSpecifier, $matched[$offset]);
            $this->assertPrefixLength($varSpecifier, $extracted);
            foreach ($extracted->variableNames() as $name) {
                $variables[$name] = $extracted->fetch($name);
            }
        }

        return ExtractionResult::success($variables);
    }

    /**
     * Matches named expression components against the variable specifiers.
     *
     * Non-exploded variables consume one matching component or may be absent.
     * Exploded variables consume one or more matching components and backtrack
     * through progressively larger matches so that subsequent variables can
     * consume their own components.
     *
     * @param list<string> $components The expression components to match.
     * @param int $componentOffset The offset of the next component to match.
     * @param int $varSpecifierOffset The offset of the next variable specifier
     *                                to match.
     *
     * @throws VariableCanNotBeExtracted
     *
     * @return list<string|null>|null One matched component sequence per variable
     *                                specifier, with `null` for an absent variable,
     *                                or `null` if the components cannot be matched
     *                                completely.
     */
    private function matchNamedValues(
        array $components,
        int $componentOffset,
        int $varSpecifierOffset,
    ): ?array {
        $varSpecifierCount = count($this->varSpecifiers);
        $componentCount = count($components);

        if ($varSpecifierOffset === $varSpecifierCount) {
            return $componentOffset === $componentCount ? [] : null;
        }

        if ($componentOffset >= $componentCount) {
            return null;
        }

        $matched = $this->matchNamedValues($components, $componentOffset + 1, $varSpecifierOffset + 1);

        return null !== $matched ? [$components[$componentOffset], ...$matched] : null;
    }

    /**
     * Extracts variables from a positional expression value.
     *
     * Values are assigned to variable specifiers in their declared order.
     * Exploded variables consume the remaining values required to leave enough
     * values for the following variable specifiers.
     *
     * @throws VariableCanNotBeExtracted If a value cannot be extracted or a value
     *                                   exceeds its variable's prefix length.
     */
    private function extractPositionalValues(string $value): ExtractionResult
    {
        /** @var non-empty-string $separator */
        $separator = $this->operator->separator();
        $values = '' === $value ? [''] : explode($separator, $value);
        $variables = [];
        $offset = 0;

        foreach ($this->varSpecifiers as $index => $varSpecifier) {
            if (!isset($values[$offset])) {
                break;
            }

            $remaining = count($this->varSpecifiers) - $index - 1;
            $length = '*' === $varSpecifier->modifier ? count($values) - $offset - $remaining : 1;
            $serialized = implode($separator, array_slice($values, $offset, $length));

            $extracted = $this->operator->extract($varSpecifier, $serialized);
            $this->assertPrefixLength($varSpecifier, $extracted);
            foreach ($extracted->variableNames() as $name) {
                $variables[$name] = $extracted->fetch($name);
            }

            $offset += $length;
        }

        return ExtractionResult::success($variables);
    }

    /**
     * Ensures that an extracted value does not exceed its variable's prefix length.
     *
     * @throws VariableCanNotBeExtracted If the extracted value exceeds the
     *                                   variable's prefix length.
     */
    private function assertPrefixLength(VarSpecifier $varSpecifier, ExtractionResult $variables): void
    {
        if (0 === $varSpecifier->position) {
            return;
        }

        $value = $variables->fetch($varSpecifier->name);
        if (null === $value || !is_string($value->value)) {
            return;
        }

        (mb_strlen($value->value) <= $varSpecifier->position) || throw new VariableCanNotBeExtracted('The value for variable "'.$varSpecifier->name.'" exceeds the prefix length.', [ExtractionErrorReason::PrefixLengthExceeded]);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @throws SyntaxError if the expression is invalid
     * @see Expression::new()
     *
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     */
    #[Deprecated(message:'use League\Uri\UriTemplate\Exppression::new() instead', since:'league/uri:7.0.0')]
    public static function createFromString(Stringable|string $expression): self
    {
        return self::new($expression);
    }
}
