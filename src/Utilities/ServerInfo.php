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
        $args = filter_var_array($server, [
            'HTTP_X_FORWARDED_PROTO' => ['filter' => FILTER_SANITIZE_STRING, 'options' => ['default' => '']],
            'HTTPS' => ['filter' => FILTER_VALIDATE_BOOLEAN, 'flags' => FILTER_NULL_ON_FAILURE],
        ]);

        if (! empty($args["HTTP_X_FORWARDED_PROTO"])) {
            return strtolower($args["HTTP_X_FORWARDED_PROTO"]).":";
        }

        if ($args["HTTPS"]) {
            return "https:";
        }

        return "http:";
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
            return static::fetchServerHostname($server["HTTP_HOST"]);
        }

        if (! isset($server["SERVER_ADDR"])) {
            throw new InvalidArgumentException("Host could not be detected");
        }

        if (filter_var($server["SERVER_ADDR"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return "[".$server["SERVER_ADDR"]."]";
        }

        return $server["SERVER_ADDR"];
    }

    /**
     * Returns the environment hostname
     *
     * @param  string $host the environment server hostname
     *                      the port info can sometimes be
     *                      associated with the hostname
     *
     * @return string
     */
    protected static function fetchServerHostname($host)
    {
        if (preg_match("/^(.*)(:\d+)$/", $host, $matches)) {
            return $matches[1];
        }

        return $host;
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
        if (! isset($server['PHP_AUTH_USER'])) {
            return '';
        }

        $info = $server['PHP_AUTH_USER'];
        if (isset($server['PHP_AUTH_PW'])) {
            $info .= ':'.$server['PHP_AUTH_PW'];
        }

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

        if (isset($server["QUERY_STRING"])) {
            $request .= "?".$server["QUERY_STRING"];
        }

        return $request;
    }
}
