<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url\Utilities;

use InvalidArgumentException;
use League\Url;
use ReflectionClass;

/**
 * A Trait to help parse an URL
 * and create a new League\Url\Url instance
 *
 * @package League.url
 * @since 4.0.0
 */
trait UrlFactory
{
    /**
     * A Factory trait fetch info from Server environment variables
     */
    use ServerInfo;

    /**
     * Create a new League\Url\Url object from the environment
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @throws \InvalidArgumentException If the URL can not be parsed
     *
     * @return Url\Url
     */
    public static function createFromServer(array $server)
    {
        return static::createFromUrl(
            static::fetchServerScheme($server).'//'
            .static::fetchServerUserInfo($server)
            .static::fetchServerHost($server)
            .static::fetchServerPort($server)
            .static::fetchServerRequestUri($server)
        );
    }

    /**
     * Create a new League\Url\Url instance from a string
     *
     * @param string $url
     *
     * @throws \InvalidArgumentException If the URL can not be parsed
     *
     * @return Url\Url
     */
    public static function createFromUrl($url)
    {
        return static::createFromComponents(static::parse($url));
    }

    /**
     * Create a new League\Url\Url instance from an array returned by
     * PHP parse_url function
     *
     * @param array $components
     *
     * @return Url\Url
     */
    public static function createFromComponents(array $components)
    {
        $components = array_merge([
            "scheme" => null, "user" => null, "pass" => null, "host" => null,
            "port" => null, "path" => null, "query" => null, "fragment" => null,
        ], $components);

        return new Url\Url(
            new Url\Scheme($components["scheme"]),
            new Url\UserInfo($components["user"], $components["pass"]),
            new Url\Host($components["host"]),
            new Url\Port($components["port"]),
            new Url\Path($components["path"]),
            new Url\Query($components["query"]),
            new Url\Fragment($components["fragment"])
        );
    }

    /**
     * Parse a string as an URL
     *
     * Parse an URL string using PHP parse_url while applying bug fixes
     *
     * @param string $url The URL to parse
     *
     * @throws InvalidArgumentException if the URL can not be parsed
     *
     * @return array
     */
    public static function parse($url)
    {
        $defaultComponents = [
            "scheme" => null, "user" => null, "pass" => null, "host" => null,
            "port" => null, "path" => null, "query" => null, "fragment" => null,
        ];
        $url = trim($url);
        $components = @parse_url($url);
        if (is_array($components)) {
            return array_merge($defaultComponents, $components);
        }

        $components = @parse_url(static::bugFixAuthority($url));
        if (is_array($components)) {
            unset($components['scheme']);
            return array_merge($defaultComponents, $components);
        }

        throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $url));
    }

    /**
     * Parse an URL bug fix for unpatched PHP version
     *
     * bug fix for https://bugs.php.net/bug.php?id=68917
     * in the following versions
     *    - PHP 5.4.7 => 5.5.24
     *    - PHP 5.6.0 => 5.6.8
     *    - HHVM all versions
     *
     * @param string $url The URL to parse
     *
     * @return array
     */
    protected static function bugFixAuthority($url)
    {
        static $is_bugged;

        if (is_null($is_bugged)) {
            $is_bugged = ! is_array(@parse_url("//a:1"));
        }

        if ($is_bugged && strpos($url, '/') === 0) {
            return 'http:'.$url;
        }

        throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $url));
    }
}
