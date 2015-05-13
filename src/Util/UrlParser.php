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
namespace League\Url\Util;

use InvalidArgumentException;

/**
 * A Trait to parse an URL string
 *
 * @package League.url
 * @since 4.0.0
 */
trait UrlParser
{
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
    protected static function parseAuthority($url)
    {
        static $is_parse_url_bugged;
        if (is_null($is_parse_url_bugged)) {
            $is_parse_url_bugged = ! is_array(@parse_url("//example.org:80"));
        }

        if ($is_parse_url_bugged &&
            strpos($url, '/') === 0 &&
            is_array($components = @parse_url('http:'.$url))
        ) {
            unset($components['scheme']);
            return $components;
        }

        return [];
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
    protected static function parseUrl($url)
    {
        $components = @parse_url($url);
        if (! empty($components)) {
            return $components;
        }

        $components = static::parseAuthority($url);
        if (! empty($components)) {
            return $components;
        }

        throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $url));
    }
}
