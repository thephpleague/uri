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

use League\Uri\Exceptions\SyntaxError;
use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use function array_fill_keys;
use function array_filter;
use function array_keys;
use function array_map;
use function explode;
use function implode;
use function rawurlencode;
use function substr;

/**
 * @internal The class exposes the internal representation of an Exression and its usage
 * @link https://www.rfc-editor.org/rfc/rfc6570#section-2.2
 */
final class Expression
{
    /** @var array<VarSpecifier> */
    private readonly array $varSpecifiers;
    /** @var array<string> */
    public readonly array $variableNames;
    public readonly string $value;

    private function __construct(private readonly Operator $operator, VarSpecifier ...$varSpecifiers)
    {
        $this->varSpecifiers = $varSpecifiers;
        $this->variableNames = array_keys(array_fill_keys(
            array_map(static fn (VarSpecifier $varSpecifier): string => $varSpecifier->name, $varSpecifiers),
            1
        ));
        $this->value = '{'.$operator->value.implode(',', array_map(
            static fn (VarSpecifier $varSpecifier): string => $varSpecifier->toString(),
            $varSpecifiers
        )).'}';
    }

    /**
     * @param array{operator:string|Operator, varSpecifiers:array<VarSpecifier>} $properties
     */
    public static function __set_state(array $properties): self
    {
        if (is_string($properties['operator'])) {
            $properties['operator'] = Operator::from($properties['operator']);
        }

        return new self($properties['operator'], ...$properties['varSpecifiers']);
    }

    /**
     * @throws SyntaxError if the expression is invalid
     */
    public static function createFromString(string $expression): self
    {
        $parts = Operator::parseExpression($expression);

        return new Expression($parts['operator'], ...array_map(
            static fn (string $varSpec): VarSpecifier => VarSpecifier::createFromString($varSpec),
            explode(',', $parts['variables'])
        ));
    }

    /**
     * Returns the expression string representation.
     *
     * @deprecated since version 6.6.0 use the readonly property instead
     * @codeCoverageIgnore
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @deprecated since version 6.6.0 use the readonly property instead
     * @codeCoverageIgnore
     *
     * @return array<string>
     */
    public function variableNames(): array
    {
        return $this->variableNames;
    }

    public function expand(VariableBag $variables): string
    {
        $parts = [];
        foreach ($this->varSpecifiers as $varSpecifier) {
            $parts[] = $this->replace($varSpecifier, $variables);
        }

        $expanded = implode($this->operator->separator(), array_filter($parts, static fn ($value): bool => '' !== $value));
        if ('' === $expanded) {
            return $expanded;
        }

        return $this->operator->first().$expanded;
    }

    /**
     * Replaces an expression with the given variables.
     *
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     */
    private function replace(VarSpecifier $varSpec, VariableBag $variables): string
    {
        $value = $variables->fetch($varSpec->name);
        if (null === $value) {
            return '';
        }

        [$expanded, $actualQuery] = $this->inject($value, $varSpec);
        if (!$actualQuery) {
            return $expanded;
        }

        if ('&' !== $this->operator->separator() && '' === $expanded) {
            return $varSpec->name;
        }

        return $varSpec->name.'='.$expanded;
    }

    /**
     * @param string|array<string> $value
     *
     * @return array{0:string, 1:bool}
     */
    private function inject(array|string $value, VarSpecifier $varSpec): array
    {
        if (is_array($value)) {
            return $this->replaceList($value, $varSpec);
        }

        if (':' === $varSpec->modifier) {
            $value = substr($value, 0, $varSpec->position);
        }

        return [$this->operator->decode($value), $this->operator->isNamed()];
    }

    /**
     * Expands an expression using a list of values.
     *
     * @param array<string> $value
     *
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     *
     * @return array{0:string, 1:bool}
     */
    private function replaceList(array $value, VarSpecifier $varSpec): array
    {
        if (':' === $varSpec->modifier) {
            throw TemplateCanNotBeExpanded::dueToUnableToProcessValueListWithPrefix($varSpec->name);
        }

        if ([] === $value) {
            return ['', false];
        }

        $pairs = [];
        $isList = array_is_list($value);
        $useQuery = $this->operator->isNamed();
        foreach ($value as $key => $var) {
            if (!$isList) {
                $key = rawurlencode((string) $key);
            }

            $var = $this->operator->decode($var);
            if ('*' === $varSpec->modifier) {
                if (!$isList) {
                    $var = $key.'='.$var;
                } elseif ($key > 0 && $useQuery) {
                    $var = $varSpec->name.'='.$var;
                }
            }

            $pairs[$key] = $var;
        }

        if ('*' === $varSpec->modifier) {
            if (!$isList) {
                // Don't prepend the value name when using the `explode` modifier with an associative array.
                $useQuery = false;
            }

            return [implode($this->operator->separator(), $pairs), $useQuery];
        }

        if (!$isList) {
            // When an associative array is encountered and the `explode` modifier is not set, then
            // the result must be a comma separated list of keys followed by their respective values.
            $retVal = [];
            foreach ($pairs as $offset => $data) {
                $retVal[$offset] = $offset.','.$data;
            }
            $pairs = $retVal;
        }

        return [implode(',', $pairs), $useQuery];
    }
}
