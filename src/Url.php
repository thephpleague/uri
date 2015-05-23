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
     * Accessible URL parts
     *
     * @var array
     */
    protected static $urlParts = [
        'scheme'   => 1,
        'userInfo' => 1,
        'host'     => 1,
        'port'     => 1,
        'path'     => 1,
        'query'    => 1,
        'fragment' => 1,
        'user'     => 1,
        'pass'     => 1,
    ];

    /**
     * A Factory trait fetch info from Server environment variables
     */
    use Utilities\ServerInfo;

    /**
     * A Factory Trait to create new URL instance
     */
    use Utilities\UrlFactory;

    /**
     * Trait for Common methods amongs composed class
     */
    use Utilities\CompositionTrait;

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
        if (! $this->port->isEmpty() && $this->hasStandardPort()) {
            $this->port = $this->port->withValue(null);
        }
    }

    /**
     * Magic read-only for all Part/Component URL properties
     *
     * @param string $part The property to read from
     *
     * @return mixed
     */
    public function __get($part)
    {
        if (! isset(static::$urlParts[$part])) {
            throw new InvalidArgumentException(sprintf('Unknown URL part : `%s`', $part));
        }

        return $this->$part;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return static::parseUrl($this) + static::$defaultComponents;
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
        return $url->__toString() == $this->__toString();
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
        return $this->withComponent('host', $host);
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        return $this->withComponent('scheme', $scheme);
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
        $clone = clone $this;
        $clone->userInfo = $userInfo;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        return $this->withComponent('port', $port);
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        return $this->withComponent('path', $path);
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        return $this->withComponent('query', $query);
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        return $this->withComponent('fragment', $fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize()
    {
        return $this->withComponent('path', $this->path->withoutDotSegments());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($url)
    {
        $rel = static::createFromUrl($url);
        if ($rel->isAbsolute()) {
            return $rel->normalize();
        }

        $auth = $rel->getAuthority();
        if (! empty($auth) && $auth != $this->getAuthority()) {
            return $rel->withScheme($this->scheme)->normalize();
        }

        $res = $this->withFragment($rel->fragment);
        if (! $rel->path->isEmpty()) {
            return $this->resolvePath($res, $rel);
        }

        if (! $rel->query->isEmpty()) {
            return $res->withQuery($rel->query)->normalize();
        }

        return $res->normalize();
    }

    /**
     * returns the resolve URL components
     *
     * @param Url $url the final URL
     * @param Url $rel the relative URL
     *
     * @return static
     */
    protected function resolvePath(Url $url, Url $rel)
    {
        $path = $rel->path;
        if (! $rel->path->isAbsolute()) {
            $segments = $url->path->toArray();
            array_pop($segments);
            $is_absolute = Path::IS_RELATIVE;
            if ($url->path->isEmpty() || $url->path->isAbsolute()) {
                $is_absolute = Path::IS_ABSOLUTE;
            }
            $path = Path::createFromArray(array_merge($segments, $rel->path->toArray()), $is_absolute);
        }

        return $url->withPath($path->withoutDotSegments())->withQuery($rel->query);
    }
}
