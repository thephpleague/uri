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
namespace League\Url;

use InvalidArgumentException;

/**
* A class to manipulate an URL as a Value Object
*
* @package League.url
* @since 4.0.0
*/
trait UrlFactoryTrait
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
        $scheme = "";
        if (isset($server["SERVER_PROTOCOL"])) {
            $scheme = explode("/", $server["SERVER_PROTOCOL"]);
            $scheme = strtolower($scheme[0]);
            if (isset($server["HTTPS"]) && "off" != $server["HTTPS"]) {
                $scheme .= "s";
            }
            $scheme .= ":";
        }

        return $scheme."//";
    }

    /**
     * Returns the environment host
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @throws new InvalidArgumentException If the host can not be detected
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
            return $server["SERVER_ADDR"];
        }

        throw new InvalidArgumentException("Host could not be detected");
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
        if (isset($server['REQUEST_URI'])) {
            return $server['REQUEST_URI'];
        }

        if (isset($server['PHP_SELF'])) {
            return $server['PHP_SELF'];
        }

        return '/';
    }
}
