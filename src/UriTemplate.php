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

use League\Uri\Contracts\UriInterface;
use League\Uri\Exceptions\UriTemplateException;
use function array_filter;
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
use function preg_replace_callback;
use function rawurlencode;
use function sprintf;
use function strpos;
use function substr;
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
            (?<variables>[^\}]+)
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
     * @var array
     */
    private $defaultVariables;

    /**
     * @var array
     */
    private $variablesNames;

    /**
     * @var UriInterface|null
     */
    private $uri;

    /**
     * @var array Variables to use in the template expansion
     */
    private $variables;

    /**
     * @param object|string $template a string or an object with the __toString method
     *
     * @throws \TypeError           if the template is not a string or an object with the __toString method
     * @throws UriTemplateException if the template syntax is invalid
     */
    public function __construct($template, array $defaultVariables = [])
    {
        $this->template = $this->filterTemplate($template);
        $this->variablesNames = $this->extractVariables();
        if ([] === $this->variablesNames) {
            $this->uri = Uri::createFromString($template);
        }
        $this->defaultVariables = $defaultVariables;
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
     * Extracts the variable name from the template.
     *
     * @throw SyntaxError if the template syntax is invalid
     *
     * @return string[]
     */
    private function extractVariables(): array
    {
        if (false === strpos($this->template, '{') && false === strpos($this->template, '}')) {
            return [];
        }

        $res = preg_match_all(self::REGEXP_EXPRESSION, $this->template, $matches, PREG_SET_ORDER);
        if (0 === $res) {
            throw UriTemplateException::dueToInvalidTemplate($this->template);
        }

        $variables = [];
        foreach ($matches as $expression) {
            $variables = $this->validateExpression($expression, $variables);
        }

        return $variables;
    }

    /**
     * Checks the expression conformance to RFC6570.
     *
     * @throws UriTemplateException if the expression does not conform to RFC6570
     */
    private function validateExpression(array $parts, array $variables): array
    {
        if ('' !== $parts['operator'] && false !== strpos(self::RESERVED_OPERATOR, $parts['operator'])) {
            throw UriTemplateException::dueToUsingReservedOperator($parts['expression']);
        }

        foreach (explode(',', $parts['variables']) as $varSpec) {
            if (1 !== preg_match(self::REGEXP_VARSPEC, $varSpec, $matches)) {
                throw UriTemplateException::dueToInvalidVariableSpecification($varSpec, $parts['expression']);
            }

            if (!in_array($matches['name'], $variables, true)) {
                $variables[] = $matches['name'];
            }
        }

        return $variables;
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
        return $this->variablesNames;
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

        return new self($template, $this->defaultVariables);
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
        if ($defaultDefaultVariables === $this->defaultVariables) {
            return $this;
        }

        $clone = clone $this;
        $clone->defaultVariables = $defaultDefaultVariables;

        return $clone;
    }

    /**
     * @throws UriTemplateException if the variable contains nested array values
     */
    public function expand(array $variables = []): UriInterface
    {
        if (null !== $this->uri) {
            return $this->uri;
        }

        $this->variables = $variables + $this->defaultVariables;

        /** @var string $uri */
        $uri = preg_replace_callback(self::REGEXP_EXPRESSION, [$this, 'expandMatch'], $this->template);

        return Uri::createFromString($uri);
    }

    /**
     * Expands the found expressions.
     *
     * @throws UriTemplateException if the variables is an array and a ":" modifier needs to be applied
     * @throws UriTemplateException if the variables contains nested array values
     */
    private function expandMatch(array $matches): string
    {
        $parsed = $this->parseExpression($matches['variables']);
        $joiner = self::OPERATOR_HASH_LOOKUP[$matches['operator']]['joiner'];
        $useQuery = self::OPERATOR_HASH_LOOKUP[$matches['operator']]['query'];

        $parts = [];
        foreach ($parsed as $part) {
            $parts[] = $this->expandExpression($part, $matches['operator'], $joiner, $useQuery);
        }

        $matchExpanded = implode($joiner, array_filter($parts));
        $prefix = self::OPERATOR_HASH_LOOKUP[$matches['operator']]['prefix'];
        if ('' !== $matchExpanded && '' !== $prefix) {
            return $prefix.$matchExpanded;
        }

        return $matchExpanded;
    }

    /**
     * Parse a template expression.
     */
    private function parseExpression(string $expression): array
    {
        $result = [];
        foreach (explode(',', $expression) as $value) {
            preg_match(self::REGEXP_VARSPEC, $value, $varSpec);
            $varSpec += ['modifier' => '', 'position' => ''];

            if ('' === $varSpec['position']) {
                $result[] = $varSpec;

                continue;
            }

            $varSpec['position'] = (int) $varSpec['position'];
            $varSpec['modifier'] = ':';

            $result[] = $varSpec;
        }

        return $result;
    }

    /**
     * Expands an expression.
     *
     * @throws UriTemplateException if the variables is an array and a ":" modifier needs to be applied
     * @throws UriTemplateException if the variables contains nested array values
     */
    private function expandExpression(array $value, string $operator, string $joiner, bool $useQuery): string
    {
        $expanded = '';
        if (!isset($this->variables[$value['name']])) {
            return $expanded;
        }

        $variable = $this->normalizeVariable($this->variables[$value['name']]);
        $actualQuery = $useQuery;
        if (is_string($variable)) {
            $expanded = self::expandString($variable, $value, $operator);
        } elseif (is_array($variable)) {
            [$expanded, $actualQuery] = self::expandList($variable, $value, $operator, $joiner, $useQuery);
        }

        if (!$actualQuery) {
            return $expanded;
        }

        if ('&' !== $joiner && '' === $expanded) {
            return $value['name'];
        }

        return $value['name'].'='.$expanded;
    }

    /**
     * @param mixed $var the value to be expanded
     *
     * @throws \TypeError if the type is not supported
     */
    private function normalizeVariable($var)
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
    private function expandString(string $variable, array $value, string $operator): string
    {
        if (':' === $value['modifier']) {
            $variable = substr($variable, 0, $value['position']);
        }

        $expanded = rawurlencode($variable);
        if ('+' === $operator || '#' === $operator) {
            return $this->decodeReserved($expanded);
        }

        return $expanded;
    }

    /**
     * Expands an expression using a list of values.
     *
     * @throws UriTemplateException if the variables is an array and a ":" modifier needs to be applied
     * @throws UriTemplateException if the variables contains nested array values
     *
     * @return array{0:string, 1:bool}
     */
    private function expandList(array $variable, array $value, string $operator, string $joiner, bool $useQuery): array
    {
        if ([] === $variable) {
            return ['', false];
        }

        $isAssoc = $this->isAssoc($variable);
        $pairs = [];
        if (':' === $value['modifier']) {
            throw UriTemplateException::dueToUnableToProcessValueListWithPrefix($value['name']);
        }

        /** @var string $key */
        foreach ($variable as $key => $var) {
            if ($isAssoc) {
                if (is_array($var)) {
                    throw UriTemplateException::dueToNestedListOfValue($key);
                }

                $key = rawurlencode((string) $key);
            }

            $var = rawurlencode((string) $var);
            if ('+' === $operator || '#' === $operator) {
                $var = $this->decodeReserved($var);
            }

            if ('*' === $value['modifier']) {
                if ($isAssoc) {
                    $var = $key.'='.$var;
                } elseif ($key > 0 && $useQuery) {
                    $var = $value['name'].'='.$var;
                }
            }

            $pairs[$key] = $var;
        }

        if ('*' === $value['modifier']) {
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
