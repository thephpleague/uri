<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Components;

/**
 * a Trait to parse a URI string
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
trait FactoryTrait
{
    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents(static::parse($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return \League\Uri\Interfaces\Schemes\Uri
     */
    public static function createFromComponents(array $components)
    {
        $components = static::formatComponents($components);

        return new static(
            new Components\Scheme($components['scheme']),
            new Components\UserInfo($components['user'], $components['pass']),
            new Components\Host($components['host']),
            new Components\Port($components['port']),
            new Components\HierarchicalPath($components['path']),
            new Components\Query($components['query']),
            new Components\Fragment($components['fragment'])
        );
    }

    /**
     * Format the components to works with all the constructors
     *
     * @param array $components
     *
     * @return array
     */
    protected static function formatComponents(array $components)
    {
        foreach ($components as $name => $value) {
            $components[$name] = (null === $value && 'port' != $name) ? '' : $value;
        }

        return array_merge([
            'scheme' => '', 'user'     => '',
            'pass'   => '', 'host'     => '',
            'port'   => null, 'path'   => '',
            'query'  => '', 'fragment' => '',
        ], $components);
    }

    /**
     * Parse a string as an URI
     *
     * Parse an URI string using PHP parse_url while applying bug fixes
     * and taking into account UTF-8
     *
     * Taken from php.net manual comments:
     *
     * @see http://php.net/manual/en/function.parse-url.php#114817
     *
     * @param string $uri The URI to parse
     *
     * @throws InvalidArgumentException if the URI can not be parsed
     *
     * @return array
     */
    public static function parse($uri)
    {
        $pattern = '%([a-zA-Z][a-zA-Z0-9+\-.]*)?(:?//)?([^:/@?&=#\[\]]+)%usD';
        $enc_uri = preg_replace_callback($pattern, function ($matches) {
            return sprintf('%s%s%s', $matches[1], $matches[2], rawurlencode($matches[3]));
        }, (string) $uri);

        foreach (['parseUri', 'parseUriFixScheme'] as $func) {
            $components = static::$func($enc_uri);
            if (!empty($components)) {
                return static::formatParsedComponents($components);
            }
        }

        throw new InvalidArgumentException(sprintf('The given URI: `%s` could not be parse', (string) $uri));
    }

    /**
     *
     * @param string $uri The URI to parse
     *
     * @return array|false
     */
    protected static function parseUri($uri)
    {
        return @parse_url($uri);
    }

    /**
     * bug fix for unpatched PHP version
     *
     * in the following versions
     *    - PHP 5.4.7 => 5.5.24
     *    - PHP 5.6.0 => 5.6.8
     *    - HHVM all versions
     *
     * We must prepend a temporary missing scheme to allow
     * parsing with parse_url function
     *
     * @see https://bugs.php.net/bug.php?id=68917
     *
     * @param string $uri The URI to parse
     *
     * @return array|false
     */
    protected static function parseUriFixScheme($uri)
    {
        if (is_array(@parse_url('//a:1')) || strpos($uri, '/') !== 0) {
            return false;
        }

        $res = @parse_url('php-bugfix-scheme:'.$uri);
        if (is_array($res)) {
            unset($res['scheme']);
        }

        return $res;
    }

    /**
     * Format and Decode UTF-8 components
     *
     * @param array $components
     *
     * @return array
     */
    protected static function formatParsedComponents(array $components)
    {
        $components = array_merge([
            'scheme' => null, 'user'     => null,
            'pass'   => null, 'host'     => null,
            'port'   => null, 'path'     => null,
            'query'  => null, 'fragment' => null,
        ], array_map('rawurldecode', $components));

        if (null !== $components['port']) {
            $components['port'] = (int) $components['port'];
        }

        return $components;
    }
}
