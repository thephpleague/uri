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
use function array_fill_keys;
use function array_filter;
use function array_keys;
use function array_map;
use function explode;
use function implode;

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

    private function __construct(public readonly Operator $operator, VarSpecifier ...$varSpecifiers)
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

        if ('' === $expanded) {
            return '';
        }

        return $this->operator->first().$expanded;
    }

    public function extract(string $uri): VariableBag
    {
        // @todo implementation
        return new VariableBag();
    }
}
