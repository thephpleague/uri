<?php

namespace League\Url;

use RuntimeException;

final class Url extends Validation
{
    /**
    * User
    *
    * @var string
    */
    private $user;

    /**
    * Pass
    *
    * @var string
    */
    private $pass;

    /**
    * Scheme
    *
    * @var string
    */
    private $scheme;

    /**
     * Port
     *
     * @var integer
     */
    private $port;

    /**
     * Fragment
     * @var string
     */
    private $fragment = '';

    /**
     * Host
     * @var array
     */
    private $host = array();

    /**
     * Path
     * @var array
     */
    private $path = array();

    /**
     * Query
     * @var array
     */
    private $query = array();

    /**
     * Query
     * @var array
     */
    private $encoding_type = self::PHP_QUERY_RFC1738;

    /**
     * The constructor
     *
     * @param string  $url           an URL
     * @param integer $encoding_type the RFC to follow when encoding the query string
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public function __construct($url, $encoding_type = self::PHP_QUERY_RFC1738)
    {
        $url = (string) $url;
        $url = trim($url);
        $components = @parse_url($url);

        if (false === $components) {
            throw new RuntimeException('The given URL could not be parse');
        }

        $components = self::sanitizeComponents($components);
        $this->encoding_type = self::validateEncodingType($encoding_type);
        $this->scheme = self::validateScheme($components['scheme']);
        $this->user = self::sanitizeComponent($components['user']);
        $this->pass = self::sanitizeComponent($components['pass']);
        $this->host = self::validateHost($components['host']);
        $this->port = self::validatePort($components['port']);
        $this->path = self::validateSegment($components['path'], '/');
        $this->query = self::validateQuery($components['query']);
        $this->fragment = self::sanitizeComponent($components['fragment']);
    }

    /**
     * Return a instance of UrlImmutable from a server array
     *
     * @param array   $server        the server array
     * @param integer $encoding_type the RFC to follow when encoding the query string
     *
     * @return self
     */
    public static function createFromServer(array $server, $encoding_type = self::PHP_QUERY_RFC1738)
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

        return new static($scheme.$host.$port.$request, $encoding_type);
    }

    /**
     * return the string representation for the current URL
     *
     * @return string
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $user = $this->getUser();
        $pass = $this->getPass();
        $host = $this->getHost();
        $port = $this->getPort();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        //Url reconstruction
        if (!empty($scheme)) {
            $scheme .= ':';
        }

        if (!empty($pass)) {
            $pass = ':'.$pass;
        }
        $user .= $pass;
        if (!empty($user)) {
            $user .='@';
        }

        if (!empty($port)) {
            $port = ':'.$port;
        }

        if (!empty($query)) {
            $query = '?'.$query;
        }
        if (!empty($fragment)) {
            $fragment = '#'.$fragment;
        }

        if (!empty($host) || !empty($scheme)) {
            $scheme .= '//';
        }

        $domain = $scheme.$user.$host.$port;
        if (!empty($path) || !empty($domain)) {
            $path = '/'.$path;
        }

        return $domain.$path.$query.$fragment;
    }

    /**
     * Set the URL user component
     *
     * @param string $str
     *
     * @return self
     */
    public function setUser($str)
    {
        $clone = clone $this;
        $clone->user = self::sanitizeComponent($str);

        return $clone;
    }

    /**
     * get the URL user component
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the URL pass component
     *
     * @param string $str
     *
     * @return self
     */
    public function setPass($str)
    {
        $clone = clone $this;
        $clone->pass = self::sanitizeComponent($str);

        return $clone;
    }

    /**
     * Return the current URL pass component
     *
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set the URL port component
     *
     * @param string $value
     *
     * @return self
     */
    public function setPort($value)
    {
        $clone = clone $this;
        $clone->port = self::validatePort($value);

        return $clone;
    }

    /**
     * Return the URL Port component
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the URL scheme component
     *
     * @param string $value
     *
     * @return self
     */
    public function setScheme($value)
    {
        $clone = clone $this;
        $clone->scheme = strtolower(self::validateScheme($value));

        return $clone;
    }

    /**
     * return the URL scheme component
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set the URL Fragment component
     *
     * @param string $str
     *
     * @return self
     */
    public function setFragment($str)
    {
        $clone = clone $this;
        $clone->fragment = self::sanitizeComponent($str);

        return $clone;
    }

    /**
     * return the URL fragment component
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Set the Query String encoding type (see {@link http_build_query})
     *
     * @param integer $encoding_type
     *
     * @return self
     */
    public function setEncodingType($encoding_type)
    {
        $clone = clone $this;
        $clone->encoding_type = self::validateEncodingType($encoding_type);

        return $clone;
    }

    /**
     * return the current Encoding type value
     *
     * @return integer
     */
    public function getEncodingType()
    {
        return $this->encoding_type;
    }

    /**
     * Set the URL query component
     *
     * @param mixed $data the data to be added to the query component
     *
     * @return self
     */
    public function setQuery($data)
    {
        $res = array();
        if (! is_null($data)) {
            $res = self::validateQuery($data);
        }
        $clone = clone $this;
        $clone->query = $res;

        return $clone;
    }

    /**
     * Return the current URL query component
     *
     * @return string
     */
    public function getQuery()
    {
        $res = self::encode($this->query, $this->encoding_type);
        if (empty($res)) {
            $res = null;
        }

        return $res;
    }

    /**
     * Replace the current URL query component
     *
     * @param mixed $data the data to be replaced
     *
     * @return self
     */
    public function modifyQuery($data)
    {
        $clone = clone $this;
        $clone->query = array_merge($this->query, self::validateQuery($data));

        return $clone;
    }

    /**
     * Set the URL host component
     *
     * @param mixed $data the host data can be a array or a string
     *
     * @return self
     */
    public function setHost($data)
    {
        $clone = clone $this;
        $clone->host =self::validateHost($data, $this->host);

        return $clone;
    }

    /**
     * Return the current Host component
     *
     * @return string
     */
    public function getHost()
    {
        $res = implode('.', $this->host);
        if (empty($res)) {
            $res = null;
        }

        return $res;
    }

    /**
     * Prepend the URL host component
     *
     * @param mixed   $data         the host data can be a array or a string
     * @param string  $whence       where the data should be prepended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function prependHost($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->host = self::prependSegment(
            $this->host,
            self::validateHost($data, $this->host),
            $whence,
            $whence_index
        );

        return $clone;
    }

    /**
     * Append the URL host component
     *
     * @param mixed   $data         the host data can be a array or a string
     * @param string  $whence       where the data should be appended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function appendHost($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->host = self::appendSegment(
            $this->host,
            self::validateHost($data, $this->host),
            $whence,
            $whence_index
        );

        return $clone;
    }

    /**
     * Remove part of the URL host component
     *
     * @param mixed $data the path data can be a array or a string
     *
     * @return self
     */
    public function removeHost($data)
    {
        $clone = clone $this;
        $clone->host = self::removeSegment($this->host, $data, '.');

        return $clone;
    }

    /**
     * Set the URL path component
     *
     * @param mixed $data the host data can be a array or a string
     *
     * @return self
     */
    public function setPath($data)
    {
        $clone = clone $this;
        $clone->path = self::validateSegment($data, '/');

        return $clone;
    }

    /**
     * return the URL current path
     *
     * @return string
     */
    public function getPath()
    {
        $res = implode('/', str_replace(' ', '%20', $this->path));
        if (empty($res)) {
            $res = null;
        }

        return $res;
    }

    /**
     * Prepend the URL path component
     *
     * @param mixed   $data         the path data can be a array or a string
     * @param string  $whence       where the data should be prepended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function prependPath($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->path = self::prependSegment(
            $this->path,
            self::validateSegment($data, '/'),
            $whence,
            $whence_index
        );

        return $clone;
    }

    /**
     * Append the URL path component
     *
     * @param mixed   $data         the path data can be a array or a string
     * @param string  $whence       where the data should be appended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function appendPath($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->path = self::appendSegment(
            $this->path,
            self::validateSegment($data, '/'),
            $whence,
            $whence_index
        );

        return $clone;
    }

    /**
     * Remove part of the URL path component
     *
     * @param mixed $data the path data can be a array or a string
     *
     * @return self
     */
    public function removePath($data)
    {
        $clone = clone $this;
        $clone->path = self::removeSegment($this->path, $data, '/');

        return $clone;
    }

    /**
     * Return a array representation of the URL
     *
     * @return array similar to PHP internal function {@link parse_url}
     */
    public function parse()
    {
        return array(
            'scheme' => $this->getScheme(),
            'user' => $this->getUser(),
            'pass' => $this->getPass(),
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'path' => $this->getPath(),
            'query' => $this->getQuery(),
            'fragment' => $this->getFragment(),
        );
    }
}
