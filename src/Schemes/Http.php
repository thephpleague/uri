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
namespace League\Uri\Schemes;

use InvalidArgumentException;
use League\Uri;

/**
 * Value object representing popular URI (http, https, ws, wss, ftp).
 *
 * @package League.uri
 * @since   1.0.0
 *
 */
class Http extends AbstractUri
{
    /**
     * Supported Schemes
     * @var array
     */
    protected static $supported_schemes = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        if ($this->scheme->isEmpty()) {
            return true;
        }

        if (!isset(static::$supported_schemes[$this->scheme->__toString()])) {
            return false;
        }

        return !($this->host->isEmpty() && !empty($this->getSchemeSpecificPart()));
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort()
    {
        if ($this->scheme->isEmpty()) {
            return false;
        }

        if ($this->port->isEmpty()) {
            return true;
        }

        return static::$supported_schemes[$this->scheme->__toString()] === $this->port->toInt();
    }

    /**
     * Create a new instance from the environment
     *
     * @param array $server the server and execution environment information array tipycally ($_SERVER)
     *
     * @throws InvalidArgumentException If the URL can not be parsed
     *
     * @return static
     */
    public static function createFromServer(array $server)
    {
        return static::createFromString(
            static::fetchServerScheme($server) . '//'
            . static::fetchServerUserInfo($server)
            . static::fetchServerHost($server)
            . static::fetchServerPort($server)
            . static::fetchServerRequestUri($server)
        );
    }

    /**
     * Returns the environment scheme
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerScheme(array $server)
    {
        $args = filter_var_array($server, [
            'HTTPS' => ['filter' => FILTER_SANITIZE_STRING, 'options' => ['default' => '']],
        ]);

        return  (empty($args['HTTPS']) || 'off' == $args['HTTPS'])  ? 'http:' : 'https:';
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
        if (isset($server['HTTP_HOST'])) {
            return static::fetchServerHostname($server['HTTP_HOST']);
        }

        if (!isset($server['SERVER_ADDR'])) {
            throw new InvalidArgumentException('Host could not be detected');
        }

        if (filter_var($server['SERVER_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return '[' . $server['SERVER_ADDR'] . ']';
        }

        return $server['SERVER_ADDR'];
    }

    /**
     * Returns the environment hostname
     *
     * @param string $host the environment server hostname
     *                     the port info can sometimes be
     *                     associated with the hostname
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
            $info .= ':' . $server['PHP_AUTH_PW'];
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
        $port = '';
        if (isset($server['SERVER_PORT'])) {
            $port = ':' . $server['SERVER_PORT'];
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

        $request = '';
        if (isset($server['PHP_SELF'])) {
            $request .= $server['PHP_SELF'];
        }

        if (isset($server['QUERY_STRING'])) {
            $request .= '?' . $server['QUERY_STRING'];
        }

        return $request;
    }
}
