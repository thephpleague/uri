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
abstract class AbstractHierarchical extends AbstractUri implements Interfaces\Schemes\HierarchicalUri
{
    /**
     * Supported Schemes
     *
     * @var array
     */
    protected static $supportedSchemes = [];

    /*
     * Component Path formatting in a URI string
     */
    use Uri\Components\PathFormatterTrait;

    /*
     * a trait to partially modify an HierarchicalUri object
     */
    use HierarchicalModifierTrait;

    /**
     * Create a new instance of URI
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
        $this->scheme = $scheme;
        $this->userInfo = $userInfo;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
        $this->assertValidObject();
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
                ->toAscii()->withoutDotSegments()->ksortQuery()->__toString() === $this
                ->toAscii()->withoutDotSegments()->ksortQuery()->__toString();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function relativize(Interfaces\Schemes\HierarchicalUri $relative)
    {
        $className = get_class($this);
        if (!$relative instanceof $className) {
            return $relative;
        }

        if (!$this->scheme->sameValueAs($relative->scheme) || $this->getAuthority() !== $relative->getAuthority()) {
            return $relative;
        }

        return $relative
                ->withScheme('')->withUserInfo('')->withHost('')->withPort('')
                ->withPath($this->path->relativize($relative->path)->__toString());
    }

    /**
     * Tell whether the Hierarchical URI is valid
     *
     * @return bool
     */
    protected function isValidHierarchicalUri()
    {
        if ($this->scheme->isEmpty()) {
            return true;
        }

        if (!isset(static::$supportedSchemes[$this->scheme->__toString()])) {
            return false;
        }

        return !($this->host->isEmpty() && !empty($this->getSchemeSpecificPart()));
    }
}
