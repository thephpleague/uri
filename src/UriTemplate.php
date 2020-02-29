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
use League\Uri\Exceptions\SyntaxError;
use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use function array_filter;
use function array_key_exists;
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
 * Defines the URI Template syntax and the process for expanding a URI Template into a URI reference.
 *
 * @link    https://tools.ietf.org/html/rfc6570
 * @package League\Uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   6.1.0
 *
 * Based on GuzzleHttp\UriTemplate class in Guzzle v6.5.
 * @link https://github.com/guzzle/guzzle/blob/6.5/src/UriTemplate.php
 */
final class UriTemplate
{
    /**
     * Expression regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc6570#section-2.2
     */
    private const REGEXP_EXPRESSION = '/\{
        (?<expression>
            (?<operator>[\.\/;\?&\=,\!@\|\+#])?
            (?<variables>[^\}]*)
        )
    \}/x';

    /**
     * Variables specification regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc6570#section-2.3
     */
    private const REGEXP_VARSPEC = '/^
        (?<name>(?:[A-z0-9_\.]|%[0-9a-fA-F]{2})+)
        (?<modifier>\:(?<position>\d+)|\*)?
    $/x';

    /**
     * Reserved Operator characters.
     *
     * @link https://tools.ietf.org/html/rfc6570#section-2.2
     */
    private const RESERVED_OPERATOR = '=,!@|';

    /**
     * Processing behavior according to the expression type operator.
     *
     * @link https://tools.ietf.org/html/rfc6570#appendix-A
     */
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
     * @var array<string, array{
     *                    pattern:string,
     *                    operator:string,
     *                    variables:array<array{
     *                    name: string,
     *                    modifier: string,
     *                    position: string
     *                    }>,
     *                    joiner:string,
     *                    prefix:string,
     *                    query:bool
     *                    }>
     */
    private $expressions;

    /**
     * @var array{noExpression:UriInterface|null, noVariables:UriInterface|null}
     */
    private $cache;

    /**
     * @var array<string,string|array>
     */
    private $variables;

    /**
     * @param object|string $template a string or an object with the __toString method
     *
     * @throws \TypeError               if the template is not a string or an object with the __toString method
     * @throws SyntaxError              if the template syntax is invalid
     * @throws TemplateCanNotBeExpanded if the template variables are invalid
     */
    public function __construct($template, array $defaultVariables = [])
    {
        $this->template = $this->filterTemplate($template);

        $this->parseExpressions();

        $this->defaultVariables = $this->filterVariables($defaultVariables);
    }

    /**
     * {@inheritDoc}
     */
    public static function __set_state(array $properties): self
    {
        return new self($properties['template'], $properties['defaultVariables']);
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
     * @throws SyntaxError if the template syntax is invalid
     */
    private function parseExpressions(): void
    {
        $this->cache = ['noExpression' => null, 'noVariables' => null];
        /** @var string $remainder */
        $remainder = preg_replace(self::REGEXP_EXPRESSION, '', $this->template);
        if (false !== strpos($remainder, '{') || false !== strpos($remainder, '}')) {
            throw new SyntaxError('The submitted template "'.$this->template.'" contains invalid expressions.');
        }

        preg_match_all(self::REGEXP_EXPRESSION, $this->template, $expressions, PREG_SET_ORDER);
        $this->expressions = [];
        $foundVariables = [];
        foreach ($expressions as $expression) {
            if (isset($this->expressions[$expression['expression']])) {
                continue;
            }

            /** @var array{expression:string, operator:string, variables:string, pattern:string} $expression */
            $expression = $expression + ['operator' => '', 'pattern' => '{'.$expression['expression'].'}'];
            [$parsedVariables, $foundVariables] = $this->parseVariableSpecification($expression, $foundVariables);
            $this->expressions[$expression['expression']] = ['variables' => $parsedVariables]
                + $expression
                + self::OPERATOR_HASH_LOOKUP[$expression['operator']];
        }

        $this->variableNames = array_keys($foundVariables);
    }

    /**
     * Parses a variable specification in conformance to RFC6570.
     *
     * @param array{expression:string, operator:string, variables:string, pattern:string} $expression
     * @param array<string,int>                                                           $foundVariables
     *
     * @throws SyntaxError if the expression does not conform to RFC6570
     *
     * @return array{0:array<array{name:string, modifier:string, position:string}>, 1:array<string,int>}
     */
    private function parseVariableSpecification(array $expression, array $foundVariables): array
    {
        $parsedVariableSpecification = [];
        if ('' !== $expression['operator'] && false !== strpos(self::RESERVED_OPERATOR, $expression['operator'])) {
            throw new SyntaxError('The operator used in the expression "'.$expression['pattern'].'" is reserved.');
        }

        foreach (explode(',', $expression['variables']) as $varSpec) {
            if ('' === $varSpec) {
                throw new SyntaxError('No variable specification was included in the expression "'.$expression['pattern'].'".');
            }

            if (1 !== preg_match(self::REGEXP_VARSPEC, $varSpec, $parsed)) {
                throw new SyntaxError('The variable specification "'.$varSpec.'" included in the expression "'.$expression['pattern'].'" is invalid.');
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
     * Filters out variables for the given template.
     *
     * @return array<string|array<string>>
     */
    private function filterVariables(array $variables): array
    {
        $filter = function ($key): bool {
            return in_array($key, $this->variableNames, true);
        };

        $result = array_filter($variables, $filter, ARRAY_FILTER_USE_KEY);
        foreach ($result as $name => &$value) {
            $value = $this->normalizeValue($name, $value, true);
        }
        unset($value);

        return $result;
    }

    /**
     * @param mixed $value the value to be expanded
     *
     * @throws \TypeError               if the type is not supported
     * @throws TemplateCanNotBeExpanded if the value contains nested list
     *
     * @return string|array<string>
     */
    private function normalizeValue(string $name, $value, bool $isNestedListAllowed)
    {
        if (is_array($value)) {
            if (!$isNestedListAllowed) {
                throw TemplateCanNotBeExpanded::dueToNestedListOfValue($name);
            }

            foreach ($value as &$var) {
                $var = $this->normalizeValue($name, $var, false);
            }
            unset($var);

            return $value;
        }

        if (is_bool($value)) {
            return true === $value ? '1' : '0';
        }

        if (null === $value || is_scalar($value) || method_exists($value, '__toString')) {
            return (string) $value;
        }

        throw new \TypeError(sprintf('The variable '.$name.' must be NULL, a scalar or a stringable object `%s` given', gettype($value)));
    }

    /**
     * The template string.
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Returns the names of the variables in the template, in order.
     *
     * @return string[]
     */
    public function getVariableNames(): array
    {
        return $this->variableNames;
    }

    /**
     * Returns the default values used to expand the template.
     *
     * The returned list only contains variables whose name is part of the current template.
     *
     * @return array<string,string|array>
     */
    public function getDefaultVariables(): array
    {
        return $this->defaultVariables;
    }

    /**
     * Returns a new instance with the updated default variables.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified default variables.
     *
     * If present, variables whose name is not part of the current template
     * possible variable names are removed.
     *
     */
    public function withDefaultVariables(array $defaultDefaultVariables): self
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
            $this->cache['noExpression'] = $this->cache['noExpression'] ?? Uri::createFromString($this->template);

            return $this->cache['noExpression'];
        }

        $this->variables = $this->filterVariables($variables) + $this->defaultVariables;
        if ([] === $this->variables) {
            $this->cache['noVariables'] = $this->cache['noVariables'] ?? Uri::createFromString(
                preg_replace(self::REGEXP_EXPRESSION, '', $this->template)
            );

            return $this->cache['noVariables'];
        }

        /** @var string $uri */
        $uri = preg_replace_callback(self::REGEXP_EXPRESSION, [$this, 'expandExpression'], $this->template);

        return Uri::createFromString($uri);
    }

    /**
     * Expands a single expression.
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

        $nullFilter = static function ($value): bool {
            return '' !== $value;
        };

        $expanded = implode($joiner, array_filter($parts, $nullFilter));
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
        if (!array_key_exists($variable['name'], $this->variables)) {
            return '';
        }

        $value = $this->variables[$variable['name']];
        $arguments = [$value, $variable, $operator];
        $method = 'expandString';
        if (is_array($value)) {
            $arguments[] = $joiner;
            $arguments[] = $useQuery;
            $method = 'expandList';
        }

        $actualQuery = $useQuery;
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
     * This assumption is a trade-off for accuracy in favor of speed, but it
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

        static $delimitersEncoded = [
            '%3A', '%2F', '%3F', '%23', '%5B', '%5D', '%40', '%21', '%24',
            '%26', '%27', '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D',
        ];

        return str_replace($delimitersEncoded, $delimiters, $str);
    }
}
