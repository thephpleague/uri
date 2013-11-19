<?php

namespace Bakame\Url;

class Url
{
    private $auth;

    private $query;

    private $host;

    private $path;

    private $fragment;

    private $scheme;

    private $port;

    private static $default_url = [
        'scheme' => null,
        'user' => null,
        'pass' => null,
        'host' => null,
        'port' => null,
        'path' => null,
        'query' => null,
        'fragment' => null,
    ];

    public static function createFromServer(array $server)
    {
        $requestUri = $server['PHP_SELF'];
        if (isset($server['REQUEST_URI'])) {
            $requestUri = $server['REQUEST_URI'];
        }
        $https = '';
        if (array_key_exists('HTTPS', $server) && 'on' == $server['HTTPS']) {
            $https = 's';
        }
        $protocol = explode('/', $server['SERVER_PROTOCOL']);
        $protocol = strtolower($protocol[0]).$https;
        $port = '';
        if (array_key_exists('SERVER_PORT', $server) && '80' != $server['SERVER_PORT']) {
            $port = ':'.$server['SERVER_PORT'];
        }

        return self::createFromString($protocol.'://'.$server['HTTP_HOST'].$port.$requestUri);
    }

    public static function createFromString($url)
    {
        $res = parse_url($url);
        if (false === $res) {
            throw new InvalidArgumentException('Invalid URL given');
        }
        $url = array_replace(self::$default_url, $res);
        //FIX FOR PHP 5.4.7- BUG
        if (null == $url['scheme'] && null == $url['host'] && 0 === strpos($url['path'], '//')) {
            $tmp = substr($url['path'], 2);
            list($url['host'], $url['path']) = explode('/', $tmp, 2);
        }

        return new static(
            new Scheme($url['scheme']),
            new Auth($url),
            new Segment($url['host'], '.'),
            new Port($url['port']),
            new Segment($url['path'], '/'),
            new Query($url['query']),
            new Fragment($url['fragment'])
        );
    }

    public function __construct(
        Scheme $scheme,
        Auth $auth,
        Segment $host,
        Port $port,
        Segment $path,
        Query $query,
        Fragment $fragment
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    public function __toString()
    {
        $url = [];
        foreach (['scheme', 'auth', 'host', 'port', 'path', 'query', 'fragment'] as $component) {
            $value = $this->{$component}->__toString();
            if ('path' == $component && ! empty($value)) {
                $value = '/'.$value;
            }
            $url[] = $value;
        }

        return implode('', $url);
    }

    public function getScheme($key = null)
    {
        return $this->scheme->get($key);
    }

    public function getAuth($key = null)
    {
        return $this->auth->get($key);
    }

    public function getHost($key = null)
    {
        return $this->host->get($key);
    }

    public function getPort($key = null)
    {
        return $this->port->get($key);
    }

    public function getPath($key = null)
    {
        return $this->path->get($key);
    }

    public function getQuery($key = null)
    {
        return $this->query->get($key);
    }

    public function getFragment($key = null)
    {
        return $this->fragment->get($key);
    }

    public function setScheme($key = null)
    {
        $this->scheme->set($key);

        return $this;
    }

    public function setAuth($key, $value = null)
    {
        $this->auth->set($key, $value);

        return $this;
    }

    public function setHost($key, $position = 'append', $keyBefore = null)
    {
        if ('prepend' != $position) {
            $position = 'append';
        }
        $this->host->set($key, $position, $keyBefore);

        return $this;
    }

    public function setPort($key)
    {
        $this->port->set($key);

        return $this;
    }

    public function setPath($key, $position = 'append', $keyBefore = null, $keyIndex = null)
    {
        if ('prepend' != $position) {
            $position = 'append';
        }
        $this->path->set($key, $position, $keyBefore, $keyIndex);

        return $this;
    }

    public function setQuery($key, $value = null)
    {
        $this->query->set($key, $value);

        return $this;
    }

    public function setFragment($key)
    {
        $this->fragment->set($key);

        return $this;
    }

    public function unsetHost($key = null)
    {
        if (null === $key) {
            $this->host->clear();

            return $this;
        }
        $this->host->remove($key);

        return $this;
    }

    public function unsetPath($key = null)
    {
        if (null === $key) {
            $this->path->clear();

            return $this;
        }
        $this->path->remove($key);

        return $this;
    }

    public function unsetAuth($key = null)
    {
        if (null === $key) {
            $this->auth->clear();

            return $this;
        }
        $this->auth->remove($key);

        return $this;
    }

    public function unsetQuery($key = null)
    {
        if (null === $key) {
            $this->query->clear();

            return $this;
        } elseif (! is_array($key)) {
            $this->query->set($key, null);

            return $this;
        }
        foreach ($key as $prop) {
            $this->query->set($prop, null);
        }

        return $this;
    }
}
