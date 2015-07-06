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
namespace League\Uri;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URL.
 *
 * @package League.url
 * @since   1.0.0
 *
 */
class Uri implements Interfaces\Uri
{
    /**
     * a Factory to create new URL instances
     */
    use Uri\Factory;

    /**
     * Component Path formatting in a URL string
     */
    use Uri\PathFormatter;

    /**
     * partially modifying an URL object
     */
    use Uri\Modifier;

    /**
     * Create a new instance of URL
     *
     * @param Interfaces\Scheme   $scheme
     * @param Interfaces\UserInfo $userInfo
     * @param Interfaces\Host     $host
     * @param Interfaces\Port     $port
     * @param Interfaces\Path     $path
     * @param Interfaces\Query    $query
     * @param Interfaces\Fragment $fragment
     */
    public function __construct(
        Interfaces\Scheme $scheme,
        Interfaces\UserInfo $userInfo,
        Interfaces\Host $host,
        Interfaces\Port $port,
        Interfaces\Path $path,
        Interfaces\Query $query,
        Interfaces\Fragment $fragment
    ) {
        $this->scheme   = $scheme;
        $this->userInfo = $userInfo;
        $this->host     = $host;
        $this->port     = $port;
        $this->path     = $path;
        $this->query    = $query;
        $this->fragment = $fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return static::parse($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $auth = $this->getAuthority();
        if (!empty($auth)) {
            $auth = '//'.$auth;
        }

        return $this->scheme->getUriComponent().$auth
            .$this->formatPath($this->path, (bool) $auth)
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        if ($this->host->isEmpty()) {
            return '';
        }

        $port = $this->port->getUriComponent();
        if ($this->hasStandardPort()) {
            $port = '';
        }

        return $this->userInfo->getUriComponent().$this->host->getUriComponent().$port;
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

        return $this->scheme->getSchemeRegistry()->getPort($this->scheme)->sameValueAs($this->port);
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return !$this->scheme->isEmpty() && !$this->host->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UriInterface $url)
    {
        if (!$url instanceof Uri) {
            try {
                $url = static::createFromString($url->__toString(), $this->scheme->getSchemeRegistry());
            } catch (InvalidArgumentException $e) {
                return false;
            }
        }

        return $url->toAscii()->ksortQuery()->__toString() === $this->toAscii()->ksortQuery()->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return $this->userInfo->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port->toInt();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        return $this->withProperty('host', $host);
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        return $this->withProperty('scheme', $scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $pass = null)
    {
        if (is_null($pass)) {
            $pass = '';
        }
        $userInfo = $this->userInfo->withUser($user)->withPass($pass);
        if ($this->userInfo->user->sameValueAs($userInfo->user)
            && $this->userInfo->pass->sameValueAs($userInfo->pass)
        ) {
            return $this;
        }
        $newInstance = clone $this;
        $newInstance->userInfo = $userInfo;

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        return $this->withProperty('port', $port);
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        return $this->withProperty('path', $path);
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        return $this->withProperty('query', $query);
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        return $this->withProperty('fragment', $fragment);
    }
}
