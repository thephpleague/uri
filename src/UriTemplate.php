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

namespace League\Uri;

use League\Uri\Contracts\UriException;
use League\Uri\Contracts\UriInterface;
use League\Uri\Contracts\UriTemplateInterface;
use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use function array_filter;
use function array_keys;
use function explode;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_scalar;
use function is_string;
use function method_exists;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function rawurlencode;
use function sprintf;
use function strpos;
use function substr;
use const ARRAY_FILTER_USE_KEY;
use const PREG_SET_ORDER;

/**
 * Expands URI templates.
 *
 * @link http://tools.ietf.org/html/rfc6570
 *
 * Based on GuzzleHttp\UriTemplate class which is removed from Guzzle7.
 * @see https://github.com/guzzle/guzzle/blob/6.5/src/UriTemplate.php
 */
final class UriTemplate implements UriTemplateInterface
{
    private const REGEXP_EXPRESSION = '/\{
        (?<expression>
            (?<operator>[\.\/;\?&\=,\!@\|\+#])?
            (?<variables>[^\}]*)
        )
    \}/x';

    private const REGEXP_VARSPEC = '/^
        (?<name>(?:[A-z0-9_\.]|%[0-9a-fA-F]{2})+)
        (?<modifier>\:(?<position>\d+)|\*)?
    $/x';

    private const RESERVED_OPERATOR = '=,!@|';

    private const OPERATOR_HASH_LOOKUP = [
        ''  => ['prefix' => '',  'joiner' => ',', 'query' => false],
        '+' => ['prefix' => '',  'joiner' => ',', 'query' => false],
        '#' => ['prefix' => '#', 'joiner' => ',', 'query' => false],
        '.' => ['prefix' => '.', 'joiner' => '.', 'query' => false],
        '/' => ['prefix' => '/', 'joiner' => '/', 'query' => false],
        ';' => ['prefix' => ';', 'joiner' => ';', 'query' => true],
        '?' => ['prefix' => '?', 'joiner' => '&', 'query' => true],
        '&' => ['prefix' => '&', 'joiner' => '&', 'query' => true],
    ];

    /**
     * @var string
     */
    private $template;

    /**
     * @var array<string,string|array>
     */
    private $defaultVariables;

    /**
     * @var string[]
     */
    private $variableNames;

    /**
     * @var array<string, array{operator: string, variables: array<array{name: string, modifier: string, position: string}>, joiner: string, prefix: string, query: bool}>
     */
    private $expressions;

    /**
     * @var UriInterface|null
     */
    private $uri;

    /**
     * @var array<string,string|array>
     */
    private $variables;

    /**
     * @param object|string $template a string or an object with the __toString method
     *
     * @throws \TypeError               if the template is not a string or an object with the __toString method
     * @throws TemplateCanNotBeExpanded if the template syntax is invalid
     */
    public function __construct($template, array $defaultVariables = [])
    {
        $this->template = $this->filterTemplate($template);
        $this->parseExpressions();

        $this->defaultVariables = $this->filterVariables($defaultVariables);
    }

    /**
     * @param object|string $template a string or an object with the __toString method
     *
     * @throws \TypeError if the template is not a string or an object with the __toString method
     */
    private function filterTemplate($template): string
    {
        if (!is_string($template) && !method_exists($template, '__toString')) {
            throw new \TypeError(sprintf('The template must be a string or a stringable object %s given.', gettype($template)));
        }

        return (string) $template;
    }

    /**
     * Parses the template expressions.
     *
     * @throws TemplateCanNotBeExpanded if the template syntax is invalid
     */
    private function parseExpressions(): void
    {
        $this->uri = null;
        /** @var string $remainder */
        $remainder = preg_replace(self::REGEXP_EXPRESSION, '', $this->template);
        if (false !== strpos($remainder, '{') || false !== strpos($remainder, '}')) {
            throw TemplateCanNotBeExpanded::dueToMalformedExpression($this->template);
        }

        preg_match_all(self::REGEXP_EXPRESSION, $this->template, $expressions, PREG_SET_ORDER);
        $this->expressions = [];
        $foundVariables = [];
        foreach ($expressions as $expression) {
            if (isset($this->expressions[$expression['expression']])) {
                continue;
            }

            /** @var array{expression:string, operator:string, variables:string} $expression */
            $expression = $expression + ['operator' => ''];
            [$parsedVariables, $foundVariables] = $this->parseVariableSpecification($expression, $foundVariables);
            $this->expressions[$expression['expression']] = [
                'operator' => $expression['operator'],
                'variables' => $parsedVariables,
            ] + self::OPERATOR_HASH_LOOKUP[$expression['operator']];
        }

        $this->variableNames = array_keys($foundVariables);
    }

    /**
     * Parses a variable specification in conformance to RFC6570.
     *
     * @param array{expression:string, operator:string, variables:string} $expression
     * @param array<string,int>                                           $foundVariables
     *
     * @throws TemplateCanNotBeExpanded if the expression does not conform to RFC6570
     *
     * @return array{0:array<array{name:string, modifier:string, position:string}>, 1:array<string,int>}
     */
    private function parseVariableSpecification(array $expression, array $foundVariables): array
    {
        $parsedVariableSpecification = [];
        if ('' !== $expression['operator'] && false !== strpos(self::RESERVED_OPERATOR, $expression['operator'])) {
            throw TemplateCanNotBeExpanded::dueToUsingReservedOperator($expression['expression']);
        }

        foreach (explode(',', $expression['variables']) as $varSpec) {
            if (1 !== preg_match(self::REGEXP_VARSPEC, $varSpec, $parsed)) {
                throw TemplateCanNotBeExpanded::dueToMalformedVariableSpecification($varSpec, $expression['expression']);
            }

            $parsed += ['modifier' => '', 'position' => ''];
            if ('' !== $parsed['position']) {
                $parsed['position'] = (int) $parsed['position'];
                $parsed['modifier'] = ':';
            }

            $foundVariables[$parsed['name']] = 1;
            $parsedVariableSpecification[] = $parsed;
        }

        /** @var array{0:array<array{name:string, modifier:string, position:string}>, 1:array<string,int>} $result */
        $result = [$parsedVariableSpecification, $foundVariables];

        return $result;
    }

    /**
     * Filter out the value whose key is not a valid variable name for the given template.
     */
    private function filterVariables(array $variables): array
    {
        $filter = function ($key): bool {
            return in_array($key, $this->variableNames, true);
        };

        return array_filter($variables, $filter, ARRAY_FILTER_USE_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * {@inheritDoc}
     */
    public function getVariableNames(): array
    {
        return $this->variableNames;
    }

    /**
     * {@inheritDoc}
     */
    public function withTemplate($template): UriTemplateInterface
    {
        $template = $this->filterTemplate($template);
        if ($template === $this->template) {
            return $this;
        }

        $clone = clone $this;
        $clone->template = $template;
        $clone->parseExpressions();

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultVariables(): array
    {
        return $this->defaultVariables;
    }

    /**
     * {@inheritDoc}
     */
    public function withDefaultVariables(array $defaultDefaultVariables): UriTemplateInterface
    {
        $defaultDefaultVariables = $this->filterVariables($defaultDefaultVariables);
        if ($defaultDefaultVariables === $this->defaultVariables) {
            return $this;
        }

        $clone = clone $this;
        $clone->defaultVariables = $defaultDefaultVariables;

        return $clone;
    }

    /**
     * @throws TemplateCanNotBeExpanded if the variable contains nested array values
     * @throws UriException             if the resulting expansion can not be converted to a UriInterface instance
     */
    public function expand(array $variables = []): UriInterface
    {
        if ([] === $this->expressions) {
            $this->uri = $this->uri ?? Uri::createFromString($this->template);

            return $this->uri;
        }

        $this->variables = $this->filterVariables($variables + $this->defaultVariables);
        if ([] === $this->variables) {
            /** @var string $uri */
            $uri = preg_replace(self::REGEXP_EXPRESSION, '', $this->template);

            return Uri::createFromString($uri);
        }

        /** @var string $uri */
        $uri = preg_replace_callback(self::REGEXP_EXPRESSION, [$this, 'expandExpression'], $this->template);

        return Uri::createFromString($uri);
    }

    /**
     * Expands the found expressions.
     *
     * @param array{expression:string, operator: string, variables:string} $foundExpression
     *
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     */
    private function expandExpression(array $foundExpression): string
    {
        $expression = $this->expressions[$foundExpression['expression']];
        $joiner = $expression['joiner'];
        $useQuery = $expression['query'];

        $parts = [];
        /** @var array{name:string, modifier:string, position:string} $variable */
        foreach ($expression['variables'] as $variable) {
            $parts[] = $this->expandVariable($variable, $expression['operator'], $joiner, $useQuery);
        }

        $expanded = implode($joiner, array_filter($parts));
        $prefix = $expression['prefix'];
        if ('' !== $expanded && '' !== $prefix) {
            return $prefix.$expanded;
        }

        return $expanded;
    }

    /**
     * Expands an expression.
     *
     * @param array{name:string, modifier:string, position:string} $variable
     *
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     */
    private function expandVariable(array $variable, string $operator, string $joiner, bool $useQuery): string
    {
        $expanded = '';
        if (!isset($this->variables[$variable['name']])) {
            return $expanded;
        }

        $variableValue = $this->normalizeValue($this->variables[$variable['name']]);
        $arguments = [$variableValue, $variable, $operator];
        $method = 'expandString';
        $actualQuery = $useQuery;
        if (is_array($variableValue)) {
            $arguments[] = $joiner;
            $arguments[] = $useQuery;
            $method = 'expandList';
        }

        $expanded = $this->$method(...$arguments);
        if (is_array($expanded)) {
            [$expanded, $actualQuery] = $expanded;
        }

        if (!$actualQuery) {
            return $expanded;
        }

        if ('&' !== $joiner && '' === $expanded) {
            return $variable['name'];
        }

        return $variable['name'].'='.$expanded;
    }

    /**
     * @param mixed $var the value to be expanded
     *
     * @throws \TypeError if the type is not supported
     *
     * @return string|array
     */
    private function normalizeValue($var)
    {
        if (is_array($var)) {
            return $var;
        }

        if (is_bool($var)) {
            return true === $var ? '1' : '0';
        }

        if (is_scalar($var) || method_exists($var, '__toString')) {
            return (string) $var;
        }

        throw new \TypeError(sprintf('The variables must be a scalar or a stringable object `%s` given', gettype($var)));
    }

    /**
     * Expands an expression using a string value.
     */
    private function expandString(string $value, array $variable, string $operator): string
    {
        if (':' === $variable['modifier']) {
            $value = substr($value, 0, $variable['position']);
        }

        $expanded = rawurlencode($value);
        if ('+' === $operator || '#' === $operator) {
            return $this->decodeReserved($expanded);
        }

        return $expanded;
    }

    /**
     * Expands an expression using a list of values.
     *
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     *
     * @return array{0:string, 1:bool}
     */
    private function expandList(array $value, array $variable, string $operator, string $joiner, bool $useQuery): array
    {
        if ([] === $value) {
            return ['', false];
        }

        $isAssoc = $this->isAssoc($value);
        $pairs = [];
        if (':' === $variable['modifier']) {
            throw TemplateCanNotBeExpanded::dueToUnableToProcessValueListWithPrefix($variable['name']);
        }

        /** @var string $key */
        foreach ($value as $key => $var) {
            if ($isAssoc) {
                if (is_array($var)) {
                    throw TemplateCanNotBeExpanded::dueToNestedListOfValue($key);
                }

                $key = rawurlencode((string) $key);
            }

            $var = rawurlencode((string) $var);
            if ('+' === $operator || '#' === $operator) {
                $var = $this->decodeReserved($var);
            }

            if ('*' === $variable['modifier']) {
                if ($isAssoc) {
                    $var = $key.'='.$var;
                } elseif ($key > 0 && $useQuery) {
                    $var = $variable['name'].'='.$var;
                }
            }

            $pairs[$key] = $var;
        }

        if ('*' === $variable['modifier']) {
            if ($isAssoc) {
                // Don't prepend the value name when using the explode
                // modifier with an associative array.
                $useQuery = false;
            }

            return [implode($joiner, $pairs), $useQuery];
        }

        if ($isAssoc) {
            // When an associative array is encountered and the
            // explode modifier is not set, then the result must be
            // a comma separated list of keys followed by their
            // respective values.
            foreach ($pairs as $offset => &$data) {
                $data = $offset.','.$data;
            }

            unset($data);
        }

        return [implode(',', $pairs), $useQuery];
    }

    /**
     * Determines if an array is associative.
     *
     * This makes the assumption that input arrays are sequences or hashes.
     * This assumption is a tradeoff for accuracy in favor of speed, but it
     * should work in almost every case where input is supplied for a URI
     * template.
     */
    private function isAssoc(array $array): bool
    {
        return [] !== $array && 0 !== array_keys($array)[0];
    }

    /**
     * Removes percent encoding on reserved characters (used with + and # modifiers).
     */
    private function decodeReserved(string $str): string
    {
        static $delimiters = [
            ':', '/', '?', '#', '[', ']', '@', '!', '$',
            '&', '\'', '(', ')', '*', '+', ',', ';', '=',
        ];

        static $delimiters_encoded = [
            '%3A', '%2F', '%3F', '%23', '%5B', '%5D', '%40', '%21', '%24',
            '%26', '%27', '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D',
        ];

        return str_replace($delimiters_encoded, $delimiters, $str);
    }
}
