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
final class UriTemplate
{
    private const REGEXP_EXPAND_PLACEHOLDER = '/\{(?<placeholder>[^\}]+)\}/';

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
    private $uriTemplate;

    /**
     * @var array
     */
    private $defaultVariables;

    /**
     * @var array Variables to use in the template expansion
     */
    private $variables;

    /**
     * @throws \TypeError if the template is not a Stringable object or a string
     */
    public function __construct($uriTemplate, array $defaultVariables = [])
    {
        if (!is_string($uriTemplate) && !method_exists($uriTemplate, '__toString')) {
            throw new \TypeError(sprintf('The template must be a string or a stringable object %s given', gettype($uriTemplate)));
        }

        $this->uriTemplate = (string) $uriTemplate;
        $this->defaultVariables = $defaultVariables;
    }

    /**
     * @throws SyntaxError if the variables contains nested array values
     */
    public function expand(array $variables = []): UriInterface
    {
        if (false === strpos($this->uriTemplate, '{')) {
            return Uri::createFromString($this->uriTemplate);
        }

        $this->variables = array_merge($this->defaultVariables, $variables);

        /** @var string $uri */
        $uri = preg_replace_callback(self::REGEXP_EXPAND_PLACEHOLDER, [$this, 'expandMatch'], $this->uriTemplate);

        return Uri::createFromString($uri);
    }

    /**
     * Process an expansion.
     *
     * @throws SyntaxError if the variables contains nested array values
     */
    private function expandMatch(array $matches): string
    {
        $parsed = $this->parseExpression($matches['placeholder']);
        $joiner = self::OPERATOR_HASH_LOOKUP[$parsed['operator']]['joiner'];
        $useQuery = self::OPERATOR_HASH_LOOKUP[$parsed['operator']]['query'];

        $parts = [];
        foreach ($parsed['values'] as $part) {
            $parts[] = $this->expandPart($part, $parsed['operator'], $joiner, $useQuery);
        }

        $matchExpanded = implode($joiner, array_filter($parts));
        $prefix = self::OPERATOR_HASH_LOOKUP[$parsed['operator']]['prefix'];
        if ('' !== $matchExpanded && '' !== $prefix) {
            return $prefix.$matchExpanded;
        }

        return $matchExpanded;
    }

    /**
     * Parse an expression into parts.
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

    private function expandPart(array $value, string $operator, string $joiner, bool $useQuery): ?string
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
            $expanded = self::expandArray($variable, $value, $operator, $joiner, $actualQuery);
        }

        if (!$actualQuery) {
            return $expanded;
        }

        if ('&' !== $joiner && '' === $expanded) {
            return $value['value'];
        }

        return $value['value'].'='.$expanded;
    }

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

    private function expandArray(array $variable, array $value, string $operator, string $joiner, bool &$useQuery): string
    {
        if ([] === $variable) {
            $useQuery = false;

            return '';
        }

        $isAssoc = $this->isAssoc($variable);
        $pairs = [];
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
     * Removes percent encoding on reserved characters (used with + and #
     * modifiers).
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
