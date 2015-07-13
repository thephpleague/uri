<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri\Uri;

use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use ReflectionClass;

/**
 * A Factory Trait to help return a new League\Uri\Uri instance
 *
 * @package League.url
 * @since   4.0.0
 */
trait Factory
{
    /**
     * Create a new League\Uri\Uri instance from an array returned by
     * PHP parse_url function
     *
     * @param Interfaces\SchemeRegistry $schemeRegistry
     * @param array                     $components
     *
     * @return Uri\Uri
     */
    public static function createFromComponents(Interfaces\SchemeRegistry $schemeRegistry, array $components)
    {
        $components = static::formatComponents($components);

        return (new ReflectionClass(get_called_class()))->newInstance(
            $schemeRegistry,
            new Uri\Scheme($components["scheme"]),
            new Uri\UserInfo($components["user"], $components["pass"]),
            new Uri\Host($components["host"]),
            new Uri\Port($components["port"]),
            new Uri\Path($components["path"]),
            new Uri\Query($components["query"]),
            new Uri\Fragment($components["fragment"])
        );
    }

    /**
     * Format the components to works with all the constructors
     *
     * @param  array $components
     *
     * @return array
     */
    protected static function formatComponents(array $components)
    {
        foreach ($components as $name => $value) {
            $components[$name] = (null === $value && 'port' != $name) ? '' : $value;
        }

        return array_merge([
            "scheme" => "", "user"     => "",
            "pass"   => "", "host"     => "",
            "port"   => null, "path"   => "",
            "query"  => "", "fragment" => "",
        ], $components);
    }


    /**
     * Parse a string as an URL
     *
     * Parse an URL string using PHP parse_url while applying bug fixes
     * and taking into account UTF-8
     *
     * Taken from php.net manual comments:
     *
     * @see http://php.net/manual/en/function.parse-url.php#114817
     *
     * @param string $url The URL to parse
     *
     * @throws InvalidArgumentException if the URL can not be parsed
     *
     * @return array
     */
    public static function parse($url)
    {
        $url     = (string) $url;
        $pattern = '%([a-zA-Z][a-zA-Z0-9+\-.]*)?(:?//)?([^:/@?&=#\[\]]+)%usD';
        $enc_url = preg_replace_callback($pattern, function ($matches) {
            return sprintf('%s%s%s', $matches[1], $matches[2], urlencode($matches[3]));
        }, $url);

        $components = @parse_url($enc_url);
        if (is_array($components)) {
            return static::formatParsedComponents($components);
        }

        $components = @parse_url(static::fixUrlScheme($enc_url));
        if (is_array($components)) {
            unset($components['scheme']);
            return static::formatParsedComponents($components);
        }

        throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $url));
    }

    /**
     * Format and Decode UTF-8 components
     *
     * @param  array $components
     *
     * @return array
     */
    protected static function formatParsedComponents(array $components)
    {
        $components = array_merge([
            "scheme" => null, "user"     => null,
            "pass"   => null, "host"     => null,
            "port"   => null, "path"     => null,
            "query"  => null, "fragment" => null,
        ], array_map('urldecode', $components));

        if (null !== $components['port']) {
            $components['port'] = (int) $components['port'];
        }

        return $components;
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
     * @param string $url The URL to parse
     *
     * @return array
     */
    protected static function fixUrlScheme($url)
    {
        static $is_bugged;

        if (is_null($is_bugged)) {
            $is_bugged = !is_array(@parse_url("//a:1"));
        }

        if (!$is_bugged || strpos($url, '/') !== 0) {
            throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $url));
        }

        return 'php-bugfix-scheme:'.$url;
    }
}
