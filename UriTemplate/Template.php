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

use BackedEnum;
use Deprecated;
use League\Uri\Exceptions\SyntaxError;
use Stringable;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function preg_match_all;
use function preg_replace;
use function str_starts_with;
use function strlen;
use function strpbrk;
use function strpos;
use function substr;

use const PREG_OFFSET_CAPTURE;
use const PREG_SET_ORDER;

/**
 * @internal The class exposes the internal representation of a Template and its usage
 */
final class Template implements Stringable
{
    /**
     * Expression regular expression pattern.
     */
    private const REGEXP_EXPRESSION_DETECTOR = '/(?<expression>\{[^}]*})/x';

    /** @var array<string> */
    public readonly array $variableNames;

    /** @var list<Expression|Literal>  */
    private readonly array $parts;

    private function __construct(public readonly string $value, Expression|Literal ...$parts)
    {
        $this->parts = array_values($parts);
        $this->variableNames = array_unique(
            array_merge(
                ...array_map(
                    static fn (Expression $expression): array => $expression->variableNames,
                    array_filter($parts, fn (Expression|Literal $p): bool => $p instanceof Expression)
                )
            )
        );
    }

    /**
     * @throws SyntaxError if the template contains invalid expressions
     * @throws SyntaxError if the template contains invalid variable specification
     */
    public static function new(BackedEnum|Stringable|string $template): self
    {
        if ($template instanceof BackedEnum) {
            $template = $template->value;
        }

        $template = (string) $template;
        /** @var string $remainder */
        $remainder = preg_replace(self::REGEXP_EXPRESSION_DETECTOR, '', $template);
        false === strpbrk($remainder, '{}') || throw new SyntaxError('The template "'.$template.'" contains invalid expressions.');

        preg_match_all(self::REGEXP_EXPRESSION_DETECTOR, $template, $founds, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $parts = [];
        $offset = 0;
        foreach ($founds as $found) {
            $expression = $found['expression'][0];
            $position = $found['expression'][1];

            if ($position > $offset) {
                $parts[] = new Literal(substr($template, $offset, $position - $offset));
            }

            $parts[] = Expression::new($expression);

            $offset = $position + strlen($found[0][0]);
        }

        if ($offset < strlen($template)) {
            $parts[] = new Literal(substr($template, $offset));
        }

        return new self($template, ...$parts);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**-----------
     * Expand API
    ------------*/

    /**
     * @throws TemplateCanNotBeExpanded if the variables are invalid
     */
    public function expand(iterable $variables = []): string
    {
        if (!$variables instanceof VariableBag) {
            $variables = new VariableBag($variables);
        }

        return $this->expandAll($variables);
    }

    /**
     * @throws TemplateCanNotBeExpanded if the variables are invalid or missing
     */
    public function expandOrFail(iterable $variables = []): string
    {
        if (!$variables instanceof VariableBag) {
            $variables = new VariableBag($variables);
        }

        $missing = array_filter($this->variableNames, fn (string $name): bool => !isset($variables[$name]));
        if ([] !== $missing) {
            throw TemplateCanNotBeExpanded::dueToMissingVariables(...$missing);
        }

        return $this->expandAll($variables);
    }

    private function expandAll(VariableBag $variables): string
    {
        return implode('', array_map(
            static fn (Literal|Expression $part): string => $part instanceof Literal ? $part->encoded : $part->expand($variables),
            $this->parts,
        ));
    }

    /**-----------
     * Extract API
    ------------*/

    public function extract(string $value): ExtractionResult
    {
        try {
            return $this->extractAll($value) ?? new ExtractionResult();
        } catch (SyntaxError $e) {
            return new ExtractionResult();
        }
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    public function extractOrFail(string $value): ExtractionResult
    {
        return $this->extractAll($value) ?? throw new VariableCanNotBeExtracted('The value "'.$value.'" does not match the template.');
    }

    public function match(string $value): bool
    {
        try {
            return null !== $this->extractAll($value);
        } catch (VariableCanNotBeExtracted) {
            return false;
        }
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    private function extractAll(string $value): ?ExtractionResult
    {
        return $this->matchParts($value, 0, 0);
    }

    /**
     * @throws VariableCanNotBeExtracted
     */
    private function matchParts(
        string $value,
        int $partOffset,
        int $valueOffset,
        ExtractionResult $variables = new ExtractionResult(),
    ): ?ExtractionResult {
        if ($partOffset === count($this->parts)) {
            return $valueOffset === strlen($value) ? $variables : null;
        }

        $part = $this->parts[$partOffset];

        if ($part instanceof Literal) {
            if (!str_starts_with(substr($value, $valueOffset), $part->encoded)) {
                return null;
            }

            return $this->matchParts($value, $partOffset + 1, $valueOffset + strlen($part->encoded), $variables);
        }

        $expressionOffset = $valueOffset;
        $prefix = $part->operator->first();

        if ('' !== $prefix && !str_starts_with(substr($value, $expressionOffset), $prefix)) {
            return null;
        }

        $expressionOffset += strlen($prefix);
        $nextLiteral = null;

        for ($offset = $partOffset + 1, $count = count($this->parts); $offset < $count; ++$offset) {
            if ($this->parts[$offset] instanceof Literal) {
                $nextLiteral = $this->parts[$offset];
                break;
            }
        }

        if (null === $nextLiteral) {
            if ($partOffset + 1 !== count($this->parts)) {
                return null;
            }

            $partValue = substr($value, $expressionOffset);

            try {
                $extracted = $part->extract($partValue);
            } catch (VariableCanNotBeExtracted) {
                return null;
            }

            return $variables->reconcile($extracted);
        }

        $position = strpos($value, $nextLiteral->encoded, $expressionOffset);
        if (false === $position) {
            return null;
        }

        $partValue = substr($value, $expressionOffset, $position - $expressionOffset);

        try {
            $extracted = $part->extract($partValue);
        } catch (VariableCanNotBeExtracted) {
            return null;
        }

        $merged = $variables->reconcile($extracted);
        if (null === $merged) {
            return null;
        }

        return $this->matchParts($value, $partOffset + 1, $position, $merged);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @throws SyntaxError if the template contains invalid expressions
     * @throws SyntaxError if the template contains invalid variable specification
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     * @see Template::new()
     *
     * Create a new instance from a string.
     *
     */
    #[Deprecated(message:'use League\Uri\UriTemplate\Template::new() instead', since:'league/uri:7.0.0')]
    public static function createFromString(Stringable|string $template): self
    {
        return self::new($template);
    }
}
