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
            if (! preg_match("/(:\d+)$/", $header, $matches)) {
                return $header;
            }

            return rtrim(substr($header, 0, -strlen($matches[1])), '.');
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
}
