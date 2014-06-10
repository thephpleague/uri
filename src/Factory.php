<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use RuntimeException;
use League\Url\Components\Scheme;
use League\Url\Components\User;
use League\Url\Components\Pass;
use League\Url\Components\Host;
use League\Url\Components\Port;
use League\Url\Components\Path;
use League\Url\Components\Query;
use League\Url\Components\Fragment;

/**
 * A Factory to ease League\Url\Url Object instantiation
 *
 * @package League.url
 */
class Factory
{
    /**
     * Return a instance of Url from a string
     *
     * @param mixed   $url          a string or an object that implement the __toString method
     * @param boolean $is_immutable should we create a Immutable object or not
     * @param integer $enc_type     the RFC to follow when encoding the query string
     *
     * @return \League\Url\UrlInterface
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromString($url, $is_immutable = false, $enc_type = PHP_QUERY_RFC1738)
    {
        $url = (string) $url;
        $url = trim($url);
        $components = @parse_url($url);

        if (false === $components) {
            throw new RuntimeException('The given URL could not be parse');
        }

        $components = self::sanitizeComponents($components);

        $obj = 'League\Url\Url';
        if ($is_immutable) {
            $obj = 'League\Url\UrlImmutable';
        }

        return new $obj(
            new Scheme($components['scheme']),
            new User($components['user']),
            new Pass($components['pass']),
            new Host($components['host']),
            new Port($components['port']),
            new Path($components['path']),
            new Query($components['query'], $enc_type),
            new Fragment($components['fragment'])
        );
    }

    /**
     * Return a instance of Url from a server array
     *
     * @param array   $server       the server array
     * @param boolean $is_immutable should we create a Immutable object or not
     * @param integer $enc_type     the RFC to follow when encoding the query string
     *
     * @return \League\Url\UrlInterface
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromServer(array $server, $is_immutable = false, $enc_type = PHP_QUERY_RFC1738)
    {
        $scheme = self::fetchServerScheme($server);
        $host =  self::fetchServerHost($server);
        $port = self::fetchServerPort($server);
        $request = self::fetchServerRequestUri($server);

        $url = $scheme.$host.$port.$request;

        return self::createFromString($url, $is_immutable, $enc_type);
    }

    /**
     * Return the Server URL scheme component
     *
     * @param array $server the server array
     *
     * @return string
     */
    protected static function fetchServerScheme(array $server)
    {
        $scheme = '';
        if (isset($server['SERVER_PROTOCOL'])) {
            $scheme = explode('/', $server['SERVER_PROTOCOL']);
            $scheme = strtolower($scheme[0]);
            if (isset($server['HTTPS']) && 'off' != $server['HTTPS']) {
                $scheme .= 's';
            }
            $scheme .= ':';
        }

        return $scheme.'//';
    }

    /**
     * Return the Server URL host component
     *
     * @param array $server the server array
     *
     * @return string
     */
    protected static function fetchServerHost(array $server)
    {
        if (isset($server['HTTP_HOST'])) {
            return $server['HTTP_HOST'];
        } elseif (isset($server['SERVER_ADDR'])) {
            return $server['SERVER_ADDR'];
        }

        throw new RuntimeException('Host could not be detected');
    }

    /**
     * Return the Server URL port component
     *
     * @param array $server the server array
     *
     * @return string
     */
    protected static function fetchServerPort(array $server)
    {
        $port = '';
        if (array_key_exists('SERVER_PORT', $server) && '80' != $server['SERVER_PORT']) {
            $port = ':'.$server['SERVER_PORT'];
        }

        return $port;
    }

    /**
     * Return the Server URL Request Uri component
     *
     * @param array $server the server array
     *
     * @return string
     */
    protected static function fetchServerRequestUri(array $server)
    {
        if (isset($server['REQUEST_URI'])) {
            return $server['REQUEST_URI'];
        } elseif (isset($server['PHP_SELF'])) {
            return $server['PHP_SELF'];
        }

        return '/';
    }

    /**
     * Sanitize URL components
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function sanitizeComponents(array $components)
    {
        $components = array_merge(array(
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ), $components);

        $components = self::formatAuthComponent($components);

        return self::formatPathComponent($components);
    }

    /**
     * Reformat the component according to the path content
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function formatPathComponent(array $components)
    {
        if (is_null($components['scheme'])
            && is_null($components['host'])
            && !empty($components['path'])
        ) {
            $tmp = $components['path'];
            if (0 === strpos($tmp, '//')) {
                $tmp = substr($tmp, 2);
            }
            $components['path'] = null;
            $res = explode('/', $tmp, 2);
            $components['host'] = $res[0];
            if (isset($res[1])) {
                $components['path'] = $res[1];
            }
            $components = self::formatHostComponent($components);
        }

        return $components;
    }

    /**
     * Reformat the component according to the host content
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function formatHostComponent(array $components)
    {
        if (strpos($components['host'], '@')) {
            list($auth, $components['host']) = explode('@', $components['host']);
            $components['user'] = $auth;
            $components['pass'] = null;
            if (false !== strpos($auth, ':')) {
                list($components['user'], $components['pass']) = explode(':', $auth);
            }
        }

        return $components;
    }

    /**
     * Reformat the component according to the auth content
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function formatAuthComponent(array $components)
    {
        if (!is_null($components['scheme'])
            && is_null($components['host'])
            && !empty($components['path'])
            && strpos($components['path'], '@') !== false
        ) {
            $tmp = explode('@', $components['path'], 2);
            $components['user'] = $components['scheme'];
            $components['pass'] = $tmp[0];
            $components['path'] = $tmp[1];
            $components['scheme'] = null;
        }

        return $components;
    }
}
