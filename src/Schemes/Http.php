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
namespace League\Uri\Schemes;

use League\Uri;
use InvalidArgumentException;

/**
 * Value object representing popular URI (http, https, ws, wss, ftp).
 *
 * @package League.url
 * @since   1.0.0
 *
 */
class Http extends Uri\Uri
{
    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        if ($this->scheme->isEmpty()) {
            return true;
        }

        return !$this->host->isEmpty();
    }

    /**
     * Create a new League\Uri\Uri object from the environment
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @throws InvalidArgumentException If the URL can not be parsed
     *
     * @return static
     */
    public static function createFromServer(array $server)
    {
        return static::createFromString(
            static::fetchServerScheme($server).'//'
            .static::fetchServerUserInfo($server)
            .static::fetchServerHost($server)
            .static::fetchServerPort($server)
            .static::fetchServerRequestUri($server)
        );
    }

    /**
     * Create a new League\Uri\Uri instance from a string
     *
     * @param string $url
     *
     * @throws \InvalidArgumentException If the URL can not be parsed
     *
     * @return static
     */
    public static function createFromString($url)
    {
        return static::createFromComponents(new Registry(['http' => 80, 'https' => 443]), static::parse($url));
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
        $args = filter_var_array($server, [
            'HTTP_X_FORWARDED_PROTO' => ['filter' => FILTER_SANITIZE_STRING, 'options' => ['default' => '']],
            'HTTPS' => ['filter' => FILTER_SANITIZE_STRING, 'options' => ['default' => '']],
        ]);

        if (!empty($args["HTTP_X_FORWARDED_PROTO"])) {
            return $args["HTTP_X_FORWARDED_PROTO"].":";
        }

        if (empty($server["HTTPS"]) || 'off' == $server["HTTPS"]) {
            return "http:";
        }

        return "https:";
    }

    /**
     * Returns the environment host
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @throws InvalidArgumentException If the host can not be detected
     *
     * @return string
     */
    protected static function fetchServerHost(array $server)
    {
        if (isset($server["HTTP_HOST"])) {
            return static::fetchServerHostname($server["HTTP_HOST"]);
        }

        if (!isset($server["SERVER_ADDR"])) {
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
        if (!isset($server['PHP_AUTH_USER'])) {
            return '';
        }

        $info = $server['PHP_AUTH_USER'];
        if (isset($server['PHP_AUTH_PW'])) {
            $info .= ':'.$server['PHP_AUTH_PW'];
        }

        if (!empty($info)) {
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
