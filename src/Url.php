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
use League\Url\Interfaces;
use League\Url\Utilities;
use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URL.
 *
 * @package League.url
 * @since 1.0.0
 *
 * @property-read Interfaces\Scheme   $scheme
 * @property-read Interfaces\UserInfo $userInfo
 * @property-read Interfaces\Host     $host
 * @property-read Interfaces\Port     $port
 * @property-read Interfaces\Path     $path
 * @property-read Interfaces\Query    $query
 * @property-read Fragment            $fragment
 */
class Url implements Interfaces\Url
{
    /**
     * Scheme Component
     *
     * @var Interfaces\Scheme
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var Interfaces\UserInfo
     */
    protected $userInfo;

    /**
     * Host Component
     *
     * @var Interfaces\Host
     */
    protected $host;

    /**
     * Port Component
     *
     * @var Interfaces\Port
     */
    protected $port;

    /**
     * Path Component
     *
     * @var Interfaces\Path
     */
    protected $path;

    /**
     * Query Component
     *
     * @var Interfaces\Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var Fragment
     */
    protected $fragment;

    /**
     * A Factory Trait to create new URL instances
     */
    use Utilities\UrlFactory;

    /**
     * Trait To get/set immutable value property
     */
    use Utilities\ImmutableValue;

    /**
     * Create a new instance of URL
     *
     * @param Interfaces\Scheme   $scheme
     * @param Interfaces\UserInfo $userInfo
     * @param Interfaces\Host     $host
     * @param Interfaces\Port     $port
     * @param Interfaces\Path     $path
     * @param Interfaces\Query    $query
     * @param Fragment            $fragment
     */
    public function __construct(
        Interfaces\Scheme $scheme,
        Interfaces\UserInfo $userInfo,
        Interfaces\Host $host,
        Interfaces\Port $port,
        Interfaces\Path $path,
        Interfaces\Query $query,
        Fragment $fragment
    ) {
        $this->scheme   = $scheme;
        $this->userInfo = $userInfo;
        $this->host     = $host;
        $this->port     = $port;
        $this->path     = $path;
        $this->query    = $query;
        $this->fragment = $fragment;
        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        if ($this->host->isEmpty()) {
            $this->userInfo = $this->userInfo->withUser(null);
            $this->port     = $this->port->withValue(null);
        }
        if (! $this->port->isEmpty() && $this->hasStandardPort()) {
            $this->port = $this->port->withValue(null);
        }
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
        if (! empty($auth)) {
            $auth = '//'.$auth;
        }

        return $this->scheme->getUriComponent().$auth
            .$this->path->format($auth)
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        if ($this->host->isEmpty()) {
            return '';
        }

        return $this->userInfo->getUriComponent()
            .$this->host->getUriComponent()
            .$this->port->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort()
    {
        return $this->scheme->hasStandardPort($this->port);
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return ! $this->scheme->isEmpty() && ! $this->host->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UriInterface $url)
    {
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

    /**
     * {@inheritdoc}
     */
    protected function normalize()
    {
        return $this->withProperty('path', $this->path->withoutDotSegments());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($url)
    {
        $relative = static::createFromUrl($url);
        if ($relative->isAbsolute()) {
            return $relative->normalize();
        }

        if (! $relative->host->isEmpty() && $relative->getAuthority() != $this->getAuthority()) {
            return $relative->withScheme($this->scheme)->normalize();
        }

        return $this->resolveRelative($relative)->normalize();
    }

    /**
     * returns the resolve URL
     *
     * @param Url $relative the relative URL
     *
     * @return static
     */
    protected function resolveRelative(Url $relative)
    {
        $newUrl = $this->withFragment($relative->fragment);
        if (! $relative->path->isEmpty()) {
            $path = $this->resolvePath($newUrl, $relative);
            return $newUrl->withPath($path)->withQuery($relative->query);
        }

        if (! $relative->query->isEmpty()) {
            return $newUrl->withQuery($relative->query);
        }

        return $newUrl;
    }

    /**
     * returns the resolve URL components
     *
     * @param Url $newUrl   the final URL
     * @param Url $relative the relative URL
     *
     * @return Path
     */
    protected function resolvePath(Url $newUrl, Url $relative)
    {
        $path = $relative->path;
        if (! $path->isAbsolute()) {
            $segments = $newUrl->path->toArray();
            array_pop($segments);
            $is_absolute = Path::IS_RELATIVE;
            if ($newUrl->path->isEmpty() || $newUrl->path->isAbsolute()) {
                $is_absolute = Path::IS_ABSOLUTE;
            }
            $path = Path::createFromArray(array_merge($segments, $path->toArray()), $is_absolute);
        }

        return $path;
    }
}
