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
use function array_unique;
use function preg_match_all;
use function preg_replace;
use function str_contains;
use const PREG_SET_ORDER;

final class Template
{
    /**
     * Expression regular expression pattern.
     */
    private const REGEXP_EXPRESSION_DETECTOR = '/\{[^}]*}/x';

    /** @var array<string, Expression> */
    private array $expressions = [];
    /** @var array<string> */
    public readonly array $variableNames;

    private function __construct(public readonly string $value, Expression ...$expressions)
    {
        $variableNames = [];
        foreach ($expressions as $expression) {
            $this->expressions[$expression->value] = $expression;
            $variableNames = [...$variableNames, ...$expression->variableNames];
        }

        $this->variableNames = array_unique($variableNames);
    }

    /**
     * @param array{value:string, template?:string, expressions:array<string, Expression>} $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['template'] ?? $properties['value'], ...array_values($properties['expressions']));
    }

    /**
     * @throws SyntaxError if the template contains invalid expressions
     * @throws SyntaxError if the template contains invalid variable specification
     */
    public static function createFromString(Stringable|string $template): self
    {
        $template = (string) $template;
        /** @var string $remainder */
        $remainder = preg_replace(self::REGEXP_EXPRESSION_DETECTOR, '', $template);
        if (str_contains($remainder, '{') || str_contains($remainder, '}')) {
            throw new SyntaxError('The template "'.$template.'" contains invalid expressions.');
        }

        $names = [];
        preg_match_all(self::REGEXP_EXPRESSION_DETECTOR, $template, $findings, PREG_SET_ORDER);
        $arguments = [];
        foreach ($findings as $finding) {
            if (!isset($names[$finding[0]])) {
                $arguments[] = Expression::createFromString($finding[0]);
                $names[$finding[0]] = 1;
            }
        }

        return new self($template, ...$arguments);
    }

    /**
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

    /**
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     */
    public function expand(VariableBag $variables): string
    {
        $uriString = $this->value;
        /** @var Expression $expression */
        foreach ($this->expressions as $pattern => $expression) {
            $uriString = str_replace($pattern, $expression->expand($variables), $uriString);
        }

        return $uriString;
    }
}
