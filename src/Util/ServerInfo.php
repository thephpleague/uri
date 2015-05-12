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
 * A Trait to fetch URI info from the Server variables
 *
 * @package League.url
 * @since 4.0.0
 */
trait ServerInfo
{

    protected static $is_parse_url_bugged;

    /**
     * Returns the environment scheme
     *
     * @param  array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerScheme(array $server)
    {
        if (isset($server["HTTP_X_FORWARDED_PROTO"]) && ! empty($server['HTTP_X_FORWARDED_PROTO'])) {
            return strtolower($server["HTTP_X_FORWARDED_PROTO"]).":";
        }

        $scheme = "http";
        if (isset($server["HTTPS"]) && "off" != $server["HTTPS"]) {
            $scheme .= "s";
        }

        return $scheme.":";
    }

    /**
     * Returns the environment host
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @throws \InvalidArgumentException If the host can not be detected
     *
     * @return string
     */
    protected static function fetchServerHost(array $server)
    {
        if (isset($server["HTTP_HOST"])) {
            $header = $server["HTTP_HOST"];
            if (preg_match("/^(.*)(:\d+)$/", $header, $matches)) {
                return $matches[1];
            }

            return $header;
        }

        if (isset($server["SERVER_ADDR"])) {
            if (filter_var($server["SERVER_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return "[".$server["SERVER_ADDR"]."]";
            }
            return $server["SERVER_ADDR"];
        }

        throw new InvalidArgumentException("Host could not be detected");
    }

    /**
     * Returns the environment user info
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerUserInfo(array $server)
    {
        $user = '';
        if (isset($server['PHP_AUTH_USER'])) {
            $user = $server['PHP_AUTH_USER'];
        }

        $pass = '';
        if (isset($server['PHP_AUTH_PW']) && ! empty($server['PHP_AUTH_PW'])) {
            $pass = ':'.$server['PHP_AUTH_PW'];
        }

        $info = $user.$pass;
        if (! empty($info)) {
            $info .= '@';
        }

        return $info;
    }

    /**
     * Returns the environment port
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerPort(array $server)
    {
        $port = "";
        if (isset($server["SERVER_PORT"])) {
            $port = ":".$server["SERVER_PORT"];
        }

        return $port;
    }

    /**
     * Returns the environment path
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerRequestUri(array $server)
    {
        if (isset($server["REQUEST_URI"])) {
            return $server["REQUEST_URI"];
        }

        $request = "";
        if (isset($server["PHP_SELF"])) {
            $request .= $server["PHP_SELF"];
        }

        if (isset($server["QUERY_STRING"]) && ! empty($server["QUERY_STRING"])) {
            $request .= "?".$server["QUERY_STRING"];
        }

        return $request;
    }

    /**
     * Parse a string as an URL
     *
     * @param  string $url The URL to parse
     *
     * @throws  InvalidArgumentException if the URL can not be parsed
     *
     * @return array
     */
    protected static function parseUrl($url)
    {
        $components = @parse_url($url);
        if (! empty($components)) {
            return $components;
        }

        if (is_null(static::$is_parse_url_bugged)) {
            static::$is_parse_url_bugged = !@parse_url("//example.org:80");
        }

        // inline fix for https://bugs.php.net/bug.php?id=68917
        if (static::$is_parse_url_bugged &&
            strpos($url, '/') === 0 &&
            is_array($components = @parse_url('http:'.$url))
        ) {
            unset($components['scheme']);
            return $components;
        }

        throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $url));
    }
}
