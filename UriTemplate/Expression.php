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
use function count;
use function explode;
use function implode;
use function is_string;
use function preg_match;

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
     * @throws VariableCanNotBeExtracted
     */
    public function extract(string $value): ExtractionResult
    {
        return $this->operator->isNamed()
            ? $this->extractNamedValue($value)
            : $this->extractPositionalValue($value);
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    private function extractPositionalValue(string $value): ExtractionResult
    {
        if ('' === $value) {
            return new ExtractionResult([
                $this->varSpecifiers[0]->name => new ExtractedValue(''),
            ]);
        }

        /** @var non-empty-string $separator */
        $separator = $this->operator->separator();
        $values = explode($separator, $value);
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
            foreach ($extracted as $name => $variable) {
                $variables[$name] = $variable;
            }

            $offset += $length;
        }

        return new ExtractionResult($variables);
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    private function extractNamedValue(string $value): ExtractionResult
    {
        if ('' === $value) {
            $variables = [];
            foreach ($this->variableNames as $name) {
                $variables[$name] = new ExtractedValue(null);
            }

            return new ExtractionResult($variables);
        }

        /** @var non-empty-string $separator */
        $separator = $this->operator->separator();
        $components = explode($separator, $value);
        $matched = $this->matchNamedValues($components, 0, 0);

        null !== $matched || throw new VariableCanNotBeExtracted('The value "'.$value.'" cannot be extracted from the expression "'.$this->value.'".');

        $variables = [];
        foreach ($this->varSpecifiers as $offset => $varSpecifier) {
            $extracted = $this->operator->extract($varSpecifier, $matched[$offset]);
            $this->assertPrefixLength($varSpecifier, $extracted);
            foreach ($extracted as $name => $variable) {
                $variables[$name] = $variable;
            }
        }

        return new ExtractionResult($variables);
    }

    /**
     * @param list<string> $components
     *
     * @return list<string|null>|null
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

        $varSpecifier = $this->varSpecifiers[$varSpecifierOffset];
        $pattern = $this->operator->extractPattern($varSpecifier);

        if (null === $pattern->exploded) {
            if ($componentOffset < $componentCount && 1 === preg_match('/\A'.$pattern->single.'\z/', $components[$componentOffset])) {
                $matched = $this->matchNamedValues($components, $componentOffset + 1, $varSpecifierOffset + 1);
                if (null !== $matched) {
                    return [$components[$componentOffset], ...$matched];
                }
            }

            /*
             * The variable may be absent.
             */
            $matched = $this->matchNamedValues($components, $componentOffset, $varSpecifierOffset + 1);

            return null === $matched ? null : [null, ...$matched];
        }

        if ($componentOffset < $componentCount) {
            /*
             * An exploded variable can be represented either by repeated
             * occurrences of its own name or by arbitrary name/value pairs.
             *
             * The first component determines which representation is being used.
             */
            $componentPattern = 1 === preg_match('/\A'.$pattern->single.'\z/', $components[$componentOffset])
                ? $pattern->single
                : $pattern->exploded;

            /*
             * Try progressively larger matches so that following variables
             * get the opportunity to consume their own components.
             */
            $maximum = $componentCount - $componentOffset;

            for ($length = 1; $length <= $maximum; ++$length) {
                if (1 !== preg_match('/\A'.$componentPattern.'\z/', $components[$componentOffset + $length - 1])) {
                    break;
                }

                $matched = $this->matchNamedValues($components, $componentOffset + $length, $varSpecifierOffset + 1);

                if (null !== $matched) {
                    return [
                        implode($this->operator->separator(), array_slice($components, $componentOffset, $length)),
                        ...$matched,
                    ];
                }
            }
        }

        /*
         * The exploded variable may be absent.
         */
        $matched = $this->matchNamedValues($components, $componentOffset, $varSpecifierOffset + 1);

        return null === $matched ? null : [null, ...$matched];
    }

    private function assertPrefixLength(VarSpecifier $varSpecifier, ExtractionResult $variables): void
    {
        if (0 === $varSpecifier->position) {
            return;
        }

        $value = $variables->fetch($varSpecifier->name);
        if (null === $value || !is_string($value->value)) {
            return;
        }

        (mb_strlen($value->value) <= $varSpecifier->position) || throw new VariableCanNotBeExtracted('The value for variable "'.$varSpecifier->name.'" exceeds the prefix length.');
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
