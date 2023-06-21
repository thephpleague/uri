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
use Stringable;
use function array_keys;
use function array_reduce;
use function preg_match_all;
use function preg_replace;
use function str_contains;
use const PREG_SET_ORDER;

final class Template
{
    /**
     * Expression regular expression pattern.
     */
    private const REGEXP_EXPRESSION_DETECTOR = '/(?<expression>\{[^}]*})/x';

    /** @var array<Expression> */
    private readonly array $expressions;
    /** @var array<string> */
    public readonly array $variableNames;

    private function __construct(public readonly string $value, Expression ...$expressions)
    {
        $this->expressions = $expressions;
        $this->variableNames = array_keys(
            array_reduce(
                $expressions,
                fn (array $curry, Expression $expression): array => [...$curry, ...array_fill_keys($expression->variableNames, 1)],
                []
            )
        );
    }

    /**
     * @throws SyntaxError if the template contains invalid expressions
     * @throws SyntaxError if the template contains invalid variable specification
     */
    public static function new(Stringable|string $template): self
    {
        $template = (string) $template;
        /** @var string $remainder */
        $remainder = preg_replace(self::REGEXP_EXPRESSION_DETECTOR, '', $template);
        if (str_contains($remainder, '{') || str_contains($remainder, '}')) {
            throw new SyntaxError('The template "'.$template.'" contains invalid expressions.');
        }

        $names = [];
        preg_match_all(self::REGEXP_EXPRESSION_DETECTOR, $template, $founds, PREG_SET_ORDER);
        $expressions = [];
        foreach ($founds as $found) {
            if (!isset($names[$found['expression']])) {
                $expressions[] = Expression::new($found['expression']);
                $names[$found['expression']] = 1;
            }
        }

        return new self($template, ...$expressions);
    }

    /**
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     */
    public function expand(VariableBag|iterable $variables): string
    {
        [$variables] = $this->filterVariables($variables);

        return $this->expandAll($variables);
    }

    /**
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     * @throws TemplateCanNotBeExpanded if a variable is missing from the input
     */
    public function expandOrFail(VariableBag|iterable $variables): string
    {
        [$variables, $missing] = $this->filterVariables($variables);
        if ([] !== $missing) {
            throw TemplateCanNotBeExpanded::dueToMissingVariables(...$missing);
        }

        return $this->expandAll($variables);
    }

    /**
     * @return array{0:VariableBag, 1:array<string>}
     */
    private function filterVariables(VariableBag|iterable $variables): array
    {
        if (!$variables instanceof VariableBag) {
            $variables = new VariableBag($variables);
        }

        $reducer = function (array $carry, int|string $name) use ($variables): array {
            if (!isset($variables[$name])) {
                $carry[] = $name;
            }

            return $carry;
        };

        return [$variables, array_reduce($this->variableNames, $reducer, [])];
    }

    private function expandAll(VariableBag $variables): string
    {
        return array_reduce(
            $this->expressions,
            fn (string $uri, Expression $expr): string => str_replace($expr->value, $expr->expand($variables), $uri),
            $this->value
        );
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
    public static function createFromString(Stringable|string $template): self
    {
        return self::new($template);
    }
}
