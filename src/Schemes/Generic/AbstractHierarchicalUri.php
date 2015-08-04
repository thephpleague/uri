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

use League\Uri\Components\Fragment;
use League\Uri\Components\Host;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo;
use League\Uri\Interfaces\Components\Collection;
use League\Uri\Interfaces\Components\Fragment as FragmentInterface;
use League\Uri\Interfaces\Components\HierarchicalPath as HierarchicalPathInterface;
use League\Uri\Interfaces\Components\Host as HostInterface;
use League\Uri\Interfaces\Components\Port as PortInterface;
use League\Uri\Interfaces\Components\Query as QueryInterface;
use League\Uri\Interfaces\Components\Scheme as SchemeInterface;
use League\Uri\Interfaces\Components\UserInfo as UserInfoInterface;
use League\Uri\Interfaces\Schemes\HierarchicalUri;

/**
 * Value object representing a Hierarchical URI.
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
abstract class AbstractHierarchicalUri extends AbstractUri implements HierarchicalUri
{
    /**
     * Create a new instance of URI
     *
     *
     * @param SchemeInterface           $scheme
     * @param UserInfoInterface         $userInfo
     * @param HostInterface             $host
     * @param PortInterface             $port
     * @param HierarchicalPathInterface $path
     * @param QueryInterface            $query
     * @param FragmentInterface         $fragment
     */
    public function __construct(
        SchemeInterface $scheme,
        UserInfoInterface $userInfo,
        HostInterface $host,
        PortInterface $port,
        HierarchicalPathInterface $path,
        QueryInterface $query,
        FragmentInterface $fragment
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
     * Supported Schemes
     *
     * @var array
     */
    protected static $supportedSchemes = [];

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        if ($this->host->isEmpty() || !$this->hasStandardPort()) {
            return parent::getAuthority();
        }

        return $this->userInfo->getUriComponent().$this->host->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function appendPath($path)
    {
        return $this->withProperty('path', $this->path->append($path));
    }

    /**
     * {@inheritdoc}
     */
    public function prependPath($path)
    {
        return $this->withProperty('path', $this->path->prepend($path));
    }

    /**
     * {@inheritdoc}
     */
    public function filterPath(callable $callable, $flag = Collection::FILTER_USE_VALUE)
    {
        return $this->withProperty('path', $this->path->filter($callable, $flag));
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension($extension)
    {
        return $this->withProperty('path', $this->path->withExtension($extension));
    }

    /**
     * {@inheritdoc}
     */
    public function withTrailingSlash()
    {
        return $this->withProperty('path', $this->path->withTrailingSlash());
    }

    /**
     * {@inheritdoc}
     */
    public function withoutTrailingSlash()
    {
        return $this->withProperty('path', $this->path->withoutTrailingSlash());
    }

    /**
     * {@inheritdoc}
     */
    public function replaceSegment($offset, $value)
    {
        return $this->withProperty('path', $this->path->replace($offset, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutSegments($offsets)
    {
        return $this->withProperty('path', $this->path->without($offsets));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutEmptySegments()
    {
        return $this->withProperty('path', $this->path->withoutEmptySegments());
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
    public function relativize(HierarchicalUri $relative)
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
        if (!parent::isValid()) {
            return false;
        }

        if ($this->scheme->isEmpty()) {
            return true;
        }

        if (!isset(static::$supportedSchemes[$this->scheme->__toString()])) {
            return false;
        }

        return !($this->host->isEmpty() && !empty($this->getSchemeSpecificPart()));
    }
}
