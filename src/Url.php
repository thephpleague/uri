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

use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URL.
 *
 * @package League.url
 * @since 1.0.0
 *
 */
class Url implements Interfaces\Url
{
    /**
     * A trait to format a path in a URL string
     */
    use Utilities\PathFormatter;

    /**
     * A Factory Trait to create new URL instances
     */
    use Utilities\UrlFactory;

    /**
     * A Modifier Trait to easily update URL instances
     */
    use Utilities\UrlModifier;

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
        return static::parse($this);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $auth     = $this->getAuthority();
        $has_auth = false;
        if (!empty($auth)) {
            $auth     = '//'.$auth;
            $has_auth = true;
        }

        return $this->scheme->getUriComponent().$auth
            .$this->formatPath($this->path, $has_auth)
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        $str = $this->__toString();

        return empty($str);
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

        return $this->userInfo->getUriComponent()
            .$this->host->getUriComponent()
            .$port;
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
        if (!$url instanceof Interfaces\Url) {
            $url = static::createFromUrl($url, $this->scheme->getSchemeRegistry());
        }

        return $url->__toString() === $this->__toString();
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
        $userInfo = $this->userInfo->withUser($user)->withPass($pass);
        if ($this->userInfo->sameValueAs($userInfo)) {
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
