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
use League\Uri\Exceptions\SyntaxError;
use function array_filter;
use function explode;
use function gettype;
use function implode;
use function is_array;
use function is_string;
use function method_exists;
use function preg_match;
use function preg_match_all;
use function preg_replace_callback;
use function rawurlencode;
use function sprintf;
use function strpos;
use function substr;
use function trim;

/**
 * Expands URI templates.
 *
 * @link http://tools.ietf.org/html/rfc6570
 *
 * Based on GuzzleHttp\UriTemplate class which is removed from Guzzle7.
 */
final class UriTemplate implements UriTemplateInterface
{
    private const REGEXP_EXPRESSION_PLACEHOLDER = '/\{(?<expression>[^\}]+)\}/';

    private const REGEXP_EXPRESSION = "/^
        (?<operator>[\.\/;\?&\=,\!@\|\+#])?
        (?<variablelist>.*)
    $/x";

    private const REGEXP_VARSPEC = "/^
        (?<varname>(?:[A-z0-9_\.]|%[0-9a-fA-F]{2})+)
        (?<modifier>(\:\d+|\*))?
    $/xi";

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
     * @var array Variables to use in the template expansion
     */
    private $variables;

    /**
     * @var UriInterface|null
     */
    private $uri;

    /**
     * @param object|string $template a string or an object with the __toString method
     *
     * @throws \TypeError if the template is not a string or an object with the __toString method
     * @throw SyntaxError if the template syntax is invalid
     */
    public function __construct($template, array $defaultVariables = [])
    {
        [$this->template, $this->uri] = $this->validateTemplate($template);
        $this->defaultVariables = $defaultVariables;
    }

    /**
     * Checks the template conformance to RFC6570.
     *
     * @param object|string $template a string or an object with the __toString method
     *
     * @throws \TypeError if the template is not a string or an object with the __toString method
     * @throw SyntaxError if the template syntax is invalid
     *
     * @return array{0:string, 1:UriInterface|null}
     */
    private function validateTemplate($template): array
    {
        if (!is_string($template) && !method_exists($template, '__toString')) {
            throw new \TypeError(sprintf('The template must be a string or a stringable object %s given.', gettype($template)));
        }

        $template = (string) $template;
        if (false === strpos($template, '{') && false === strpos($template, '}')) {
            return [$template, Uri::createFromString($template)];
        }

        $res = preg_match_all(self::REGEXP_EXPRESSION_PLACEHOLDER, $template, $matches);
        if (0 === $res) {
            throw new SyntaxError(sprintf('The submitted template "%s" contains invalid or unsupported placeholders.', $template));
        }

        foreach ($matches['expression'] as $expression) {
            $this->validateExpression($expression);
        }

        return [$template, null];
    }

    /**
     * Checks the expression conformance to RFC6570.
     *
     * @throws SyntaxError if the expression does not conform to RFC6570
     */
    private function validateExpression(string $expression): void
    {
        preg_match(self::REGEXP_EXPRESSION, $expression, $parts);
        if ('' !== $parts['operator'] && false !== strpos(self::RESERVED_OPERATOR, $parts['operator'])) {
            throw new SyntaxError(sprintf('The operator "%s" used in the expression "{%s}" is reserved.', $parts['operator'], $expression));
        }

        foreach (explode(',', $parts['variablelist']) as $varSpec) {
            if (1 !== preg_match(self::REGEXP_VARSPEC, $varSpec)) {
                throw new SyntaxError(sprintf('The variable included in the expression "{%s}" is invalid.', $expression));
            }
        }
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
    public function getDefaultVariables(): array
    {
        return $this->defaultVariables;
    }

    /**
     * @throws SyntaxError if the variables contains nested array values
     */
    public function expand(array $variables = []): UriInterface
    {
        if (null !== $this->uri) {
            return $this->uri;
        }

        $this->variables = $variables + $this->defaultVariables;

        /** @var string $uri */
        $uri = preg_replace_callback(self::REGEXP_EXPRESSION_PLACEHOLDER, [$this, 'expandMatch'], $this->template);

        return Uri::createFromString($uri);
    }

    /**
     * Expands the found expressions.
     *
     * @throws SyntaxError if the variables is an array and a ":" modifier needs to be applied
     * @throws SyntaxError if the variables contains nested array values
     */
    private function expandMatch(array $matches): string
    {
        $parsed = $this->parseExpression($matches['expression']);
        $joiner = self::OPERATOR_HASH_LOOKUP[$parsed['operator']]['joiner'];
        $useQuery = self::OPERATOR_HASH_LOOKUP[$parsed['operator']]['query'];

        $parts = [];
        foreach ($parsed['values'] as $part) {
            $parts[] = $this->expandExpression($part, $parsed['operator'], $joiner, $useQuery);
        }

        $matchExpanded = implode($joiner, array_filter($parts));
        $prefix = self::OPERATOR_HASH_LOOKUP[$parsed['operator']]['prefix'];
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
        $result['operator'] = '';
        if (isset(self::OPERATOR_HASH_LOOKUP[$expression[0]])) {
            $result['operator'] = $expression[0];
            $expression = substr($expression, 1);
        }

        foreach (explode(',', $expression) as $value) {
            $value = trim($value);
            $varSpec = ['value' => $value, 'modifier' => ''];
            $colonPos = strpos($value, ':');
            if (false !== $colonPos) {
                $varSpec['value'] = substr($value, 0, $colonPos);
                $varSpec['modifier'] = ':';
                $varSpec['position'] = (int) substr($value, $colonPos + 1);
            } elseif ('*' === substr($value, -1)) {
                $varSpec['modifier'] = '*';
                $varSpec['value'] = substr($value, 0, -1);
            }

            $result['values'][] = $varSpec;
        }

        return $result;
    }

    /**
     * Expands an expression.
     *
     * @throws SyntaxError if the variables is an array and a ":" modifier needs to be applied
     * @throws SyntaxError if the variables contains nested array values
     */
    private function expandExpression(array $value, string $operator, string $joiner, bool $useQuery): ?string
    {
        if (!isset($this->variables[$value['value']])) {
            return null;
        }

        $expanded = '';
        $variable = $this->variables[$value['value']];
        $actualQuery = $useQuery;

        if (is_scalar($variable)) {
            $variable = (string) $variable;
            $expanded = self::expandString($variable, $value, $operator);
        } elseif (is_array($variable)) {
            $expanded = self::expandList($variable, $value, $operator, $joiner, $actualQuery);
        }

        if (!$actualQuery) {
            return $expanded;
        }

        if ('&' !== $joiner && '' === $expanded) {
            return $value['value'];
        }

        return $value['value'].'='.$expanded;
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
     * @throws SyntaxError if the variables is an array and a ":" modifier needs to be applied
     * @throws SyntaxError if the variables contains nested array values
     */
    private function expandList(array $variable, array $value, string $operator, string $joiner, bool &$useQuery): string
    {
        if ([] === $variable) {
            $useQuery = false;

            return '';
        }

        $isAssoc = $this->isAssoc($variable);
        $pairs = [];
        if (':' === $value['modifier']) {
            throw new SyntaxError(sprintf('The ":" modifier can not be applied on the "%s" variable which is a list.', $value['value']));
        }

        foreach ($variable as $key => $var) {
            if ($isAssoc) {
                if (is_array($var)) {
                    throw new SyntaxError(sprintf('The submitted value for `%s` can not be a nested array.', $key));
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
                    $var = $value['value'].'='.$var;
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

            return implode($joiner, $pairs);
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

        return implode(',', $pairs);
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
