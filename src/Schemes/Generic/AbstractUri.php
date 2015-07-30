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
use League\Uri;
use League\Uri\Interfaces;
use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URI.
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
abstract class AbstractUri implements Interfaces\Schemes\Uri
{
    /**
     * Scheme Component
     *
     * @var Interfaces\Components\Scheme
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var Interfaces\Components\UserInfo
     */
    protected $userInfo;

    /**
     * Host Component
     *
     * @var Interfaces\Components\Host
     */
    protected $host;

    /**
     * Port Component
     *
     * @var Interfaces\Components\Port
     */
    protected $port;

    /**
     * Query Component
     *
     * @var Interfaces\Components\Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var Interfaces\Components\Fragment
     */
    protected $fragment;

    /*
     * Trait To get/set immutable value property
     */
    use Uri\Types\ImmutablePropertyTrait;

    /*
     * Component Path formatting in a URI string
     */
    use Uri\Components\PathFormatterTrait;

    /*
     * Trait To get/set immutable value property
     */
    use ParserTrait;

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme->__toString();
    }

    /**
     * Get URI relative reference
     *
     * @return string
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
    public function filterQuery(callable $callable, $flag = Interfaces\Components\Collection::FILTER_USE_VALUE)
    {
        return $this->withProperty('query', $this->query->filter($callable, $flag));
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
    public function toArray()
    {
        return static::parse($this->__toString());
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
    public function sameValueAs($uri)
    {
        if (!$uri instanceof UriInterface && !$uri instanceof Interfaces\Schemes\Uri) {
            throw new InvalidArgumentException(
                'You must provide an object implementing the `Psr\Http\Message\UriInterface` or
                the `League\Uri\Interfaces\Schemes\Uri` interface'
            );
        }

        try {
            return static::createFromComponents(static::parse($uri->__toString()))
                ->toAscii()->ksortQuery()->__toString() === $this
                ->toAscii()->ksortQuery()->__toString();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Interfaces\Schemes\Uri $relative)
    {
        if (!$relative instanceof Interfaces\Schemes\HierarchicalUri) {
            return $relative;
        }

        if (!empty($relative->getScheme())) {
            return $relative->withoutDotSegments();
        }

        if (!empty($relative->getHost())) {
            return $this->resolveAuthority($relative)->withoutDotSegments();
        }

        return $this->resolveRelative($relative)->withoutDotSegments();
    }

    /**
     * Returns the resolve URI according to the authority
     *
     * @param Interfaces\Schemes\HierarchicalUri $relative the relative URI
     *
     * @return Interfaces\Schemes\HierarchicalUri
     */
    protected function resolveAuthority(Interfaces\Schemes\HierarchicalUri $relative)
    {
        $className = get_class($this);
        if (!$relative instanceof $className) {
            return $relative;
        }

        return $relative->withScheme($this->scheme);
    }

    /**
     * returns the resolve URI
     *
     * @param Interfaces\Schemes\HierarchicalUri $relative the relative URI
     *
     * @return static
     */
    protected function resolveRelative(Interfaces\Schemes\HierarchicalUri $relative)
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
     * @param Interfaces\Schemes\HierarchicalUri $newUri   the final URI
     * @param Interfaces\Schemes\HierarchicalUri $relative the relative URI
     *
     * @return Interfaces\Components\Path
     */
    protected function resolvePath(
        Interfaces\Schemes\HierarchicalUri $newUri,
        Interfaces\Schemes\HierarchicalUri $relative
    ) {
        $path = $relative->path;
        if ($path->isAbsolute()) {
            return $path;
        }

        $segments = $newUri->path->toArray();
        array_pop($segments);
        $isAbsolute = Uri\Components\HierarchicalPath::IS_RELATIVE;
        if ($newUri->path->isEmpty() || $newUri->path->isAbsolute()) {
            $isAbsolute = Uri\Components\HierarchicalPath::IS_ABSOLUTE;
        }

        return Uri\Components\HierarchicalPath::createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
    }
}
