<?php

namespace League\Url;

use RuntimeException;
use League\Url\Components\Scheme;
use League\Url\Components\Component;
use League\Url\Components\Host;
use League\Url\Components\Port;
use League\Url\Components\Path;
use League\Url\Components\Query;

class Factory
{
    /**
     * The constructor
     *
     * @param mixed   $url           an URL as a string or
     *                               as an object that implement the __toString method
     * @param integer $encoding_type the RFC to follow when encoding the query string
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromString($url, $encoding_type = Query::PHP_QUERY_RFC1738)
    {
        $url = (string) $url;
        $url = trim($url);
        $components = @parse_url($url);

        if (false === $components) {
            throw new RuntimeException('The given URL could not be parse');
        }

        $components = self::sanitizeComponents($components);

        return new Url(
            new Scheme($components['scheme']),
            new Component($components['user']),
            new Component($components['pass']),
            new Host($components['host']),
            new Port($components['port']),
            new Path($components['path']),
            new Query($components['query'], $encoding_type),
            new Component($components['fragment'])
        );
    }

    /**
     * Return a instance of UrlImmutable from a server array
     *
     * @param array   $server        the server array
     * @param integer $encoding_type the RFC to follow when encoding the query string
     *
     * @return self
     */
    public static function createFromServer(array $server, $encoding_type = Query::PHP_QUERY_RFC1738)
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
        $scheme .= '//';

        $host = $server['SERVER_ADDR'];
        if (isset($server['HTTP_HOST'])) {
            $host = $server['HTTP_HOST'];
        }

        $port = '';
        if (array_key_exists('SERVER_PORT', $server) && '80' != $server['SERVER_PORT']) {
            $port = ':'.$server['SERVER_PORT'];
        }

        $request = $server['PHP_SELF'];
        if (isset($server['REQUEST_URI'])) {
            $request = $server['REQUEST_URI'];
        }

        return self::createFromString($scheme.$host.$port.$request, $encoding_type);
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

        if (is_null($components['scheme']) && is_null($components['host']) && !empty($components['path'])) {
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
            if (strpos($components['host'], '@')) {
                list($auth, $components['host']) = explode('@', $components['host']);
                $components['user'] = $auth;
                $components['pass'] = null;
                if (false !== strpos($auth, ':')) {
                    list($components['user'], $components['pass']) = explode(':', $auth);
                }
            }
        }

        return $components;
    }
}
