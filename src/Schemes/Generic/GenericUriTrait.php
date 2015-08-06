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
namespace League\Uri\Schemes\Generic;

use Exception;
use InvalidArgumentException;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\PathFormatterTrait;
use League\Uri\Interfaces\Components\Collection;
use League\Uri\Interfaces\Components\Fragment as FragmentInterface;
use League\Uri\Interfaces\Components\HierarchicalPath as HierarchicalPathInterface;
use League\Uri\Interfaces\Components\Host as HostInterface;
use League\Uri\Interfaces\Components\Port as PortInterface;
use League\Uri\Interfaces\Components\Query as QueryInterface;
use League\Uri\Interfaces\Components\Scheme as SchemeInterface;
use League\Uri\Interfaces\Components\UserInfo as UserInfoInterface;
use League\Uri\Interfaces\Schemes\HierarchicalUri;
use League\Uri\Interfaces\Schemes\Uri;
use League\Uri\Parser;
use League\Uri\Types\ImmutablePropertyTrait;
use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URI.
 *
 * @package League.uri
 * @since   4.0.0
 *
 * @property-read SchemeInterface   $scheme
 * @property-read UserInfoInterface $userInfo
 * @property-read HostInterface     $host
 * @property-read PortInterface     $port
 * @property-read PathInterface     $path
 * @property-read QueryInterface    $query
 * @property-read FragmentInterface $fragment
 */
trait GenericUriTrait
{
    use ImmutablePropertyTrait;

    use PathFormatterTrait;

    /**
     * Scheme Component
     *
     * @var SchemeInterface
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var UserInfoInterface
     */
    protected $userInfo;

    /**
     * Host Component
     *
     * @var HostInterface
     */
    protected $host;

    /**
     * Port Component
     *
     * @var PortInterface
     */
    protected $port;

    /**
     * Path Component
     *
     * @var PathInterface
     */
    protected $path;

    /**
     * Query Component
     *
     * @var QueryInterface
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var FragmentInterface
     */
    protected $fragment;

    /**
     * Supported Schemes
     *
     * @var array
     */
    protected static $supportedSchemes = [];

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
        return $this->hasStandardPort() ? null : $this->port->toInt();
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort()
    {
        if ($this->port->isEmpty()) {
            return true;
        }

        if ($this->scheme->isEmpty()) {
            return false;
        }

        return static::$supportedSchemes[$this->scheme->__toString()] === $this->port->toInt();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        if ($this->host->isEmpty()) {
            return '';
        }

        $port = '';
        if (!$this->hasStandardPort()) {
            $port = $this->port->getUriComponent();
        }

        return $this->userInfo->getUriComponent().$this->host->getUriComponent().$port;
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
    public function withScheme($scheme)
    {
        return $this->withProperty('scheme', $scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $pass = null)
    {
        if (null === $pass) {
            $pass = '';
        }
        $userInfo = $this->userInfo->withUser($user)->withPass($pass);
        if ($this->userInfo->user->sameValueAs($userInfo->user)
            && $this->userInfo->pass->sameValueAs($userInfo->pass)
        ) {
            return $this;
        }
        $clone = clone $this;
        $clone->userInfo = $userInfo;

        return $clone;
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
    public function withoutDotSegments()
    {
        return $this->withProperty('path', $this->path->withoutDotSegments());
    }

    /**
     * {@inheritdoc}
     */
    public function mergeQuery($query)
    {
        return $this->withProperty('query', $this->query->merge($query));
    }

    /**
     * {@inheritdoc}
     */
    public function ksortQuery($sort = SORT_REGULAR)
    {
        return $this->withProperty('query', $this->query->ksort($sort));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutQueryValues($offsets)
    {
        return $this->withProperty('query', $this->query->without($offsets));
    }

    /**
     * {@inheritdoc}
     */
    public function filterQuery(callable $callable, $flag = Collection::FILTER_USE_VALUE)
    {
        return $this->withProperty('query', $this->query->filter($callable, $flag));
    }

    /**
     * {@inheritdoc}
     */
    public function appendHost($host)
    {
        return $this->withProperty('host', $this->host->append($host));
    }

    /**
     * {@inheritdoc}
     */
    public function prependHost($host)
    {
        return $this->withProperty('host', $this->host->prepend($host));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutZoneIdentifier()
    {
        return $this->withProperty('host', $this->host->withoutZoneIdentifier());
    }

    /**
     * {@inheritdoc}
     */
    public function toUnicode()
    {
        return $this->withProperty('host', $this->host->toUnicode());
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        return $this->withProperty('host', $this->host->toAscii());
    }

    /**
     * {@inheritdoc}
     */
    public function replaceLabel($offset, $value)
    {
        return $this->withProperty('host', $this->host->replace($offset, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutLabels($offsets)
    {
        return $this->withProperty('host', $this->host->without($offsets));
    }

    /**
     * {@inheritdoc}
     */
    public function filterHost(callable $callable, $flag = Collection::FILTER_USE_VALUE)
    {
        return $this->withProperty('host', $this->host->filter($callable, $flag));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->scheme->getUriComponent().$this->getSchemeSpecificPart();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemeSpecificPart()
    {
        $auth = $this->getAuthority();
        if (!empty($auth)) {
            $auth = '//'.$auth;
        }

        return $auth
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
    public function toArray()
    {
        return (new Parser())->parseUri($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs($uri)
    {
        if (!$uri instanceof UriInterface && !$uri instanceof Uri) {
            throw new InvalidArgumentException(
                'You must provide an object implementing the `Psr\Http\Message\UriInterface` or
                the `League\Uri\Interfaces\Schemes\Uri` interface'
            );
        }

        try {
            return static::createFromComponents((new Parser())->parseUri($uri->__toString()))
                ->toAscii()->ksortQuery()->withoutDotSegments()->__toString() === $this
                ->toAscii()->ksortQuery()->withoutDotSegments()->__toString();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Uri $relative)
    {
        $className = get_class($this);
        if (!$relative instanceof $className) {
            return $relative;
        }

        if (!empty($relative->getScheme())) {
            return $relative->withoutDotSegments();
        }

        if (!empty($relative->getHost())) {
            return $relative->withScheme($this->scheme)->withoutDotSegments();
        }

        return $this->resolveRelative($relative)->withoutDotSegments();
    }

    /**
     * returns the resolve URI
     *
     * @param HierarchicalUri $relative the relative URI
     *
     * @return static
     */
    protected function resolveRelative(Uri $relative)
    {
        $newUri = $this->withFragment($relative->fragment->__toString());
        if (!$relative->path->isEmpty()) {
            return $newUri
                ->withPath($this->resolvePath($newUri, $relative)->__toString())
                ->withQuery($relative->query->__toString());
        }

        if (!$relative->query->isEmpty()) {
            return $newUri->withQuery($relative->query->__toString());
        }

        return $newUri;
    }

    /**
     * returns the resolve URI components
     *
     * @param Uri $newUri   the final URI
     * @param Uri $relative the relative URI
     *
     * @return HierarchicalPathInterface
     */
    protected function resolvePath(Uri $newUri, Uri $relative)
    {
        $path = $relative->path;
        if (!$path instanceof HierarchicalPathInterface || $path->isAbsolute()) {
            return $path;
        }

        $segments = $newUri->path->toArray();
        array_pop($segments);
        $isAbsolute = HierarchicalPath::IS_RELATIVE;
        if ($newUri->path->isEmpty() || $newUri->path->isAbsolute()) {
            $isAbsolute = HierarchicalPath::IS_ABSOLUTE;
        }

        return $newUri->path->createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
    }

    /**
     * Check if a URI is valid
     *
     * @return bool
     */
    protected function isValidGenericUri()
    {
        $path = $this->path->getUriComponent();
        if (false === strpos($path, ':')) {
            return true;
        }
        $path = explode(':', $path);
        $path = array_shift($path);

        return !(empty($this->scheme->getUriComponent().$this->getAuthority()) && strpos($path, '/') === false);
    }
}
