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
     * Create a new League\Url\Url instance from a string
     *
     * @param  string $url
     *
     * @throws new InvalidArgumentException If the URL can not be parsed
     *
     * @return League\Url\Url
     */
    public static function createFromUrl($url)
    {
        $url = trim($url);
        $original_url = $url;
        $components = @parse_url($url);
        if (false === $components) {
            throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $original_url));
        }
        $components = array_merge([
            "scheme" => null,
            "user" => null,
            "pass" => null,
            "host" => null,
            "port" => null,
            "path" => null,
            "query" => null,
            "fragment" => null,
        ], $components);

        return new static(
            new Scheme($components["scheme"]),
            new User($components["user"]),
            new Pass($components["pass"]),
            new Host($components["host"]),
            new Port($components["port"]),
            new Path($components["path"]),
            new Query($components["query"]),
            new Fragment($components["fragment"])
        );
    }

    /**
     * Create a new League\Url\Url object from the environment
     *
     * @param  array  $server the environment server typically $_SERVER
     *
     * @throws new InvalidArgumentException If the URL can not be parsed
     *
     * @return League\Url\Url
     */
    public static function createFromServer(array $server)
    {
        $scheme  = static::fetchServerScheme($server);
        $host    = static::fetchServerHost($server);
        $port    = static::fetchServerPort($server);
        $request = static::fetchServerRequestUri($server);

        return static::createFromUrl($scheme.$host.$port.$request);
    }

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

            return substr($header, 0, -strlen($matches[1]));
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
