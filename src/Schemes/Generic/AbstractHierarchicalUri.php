<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Components\Fragment;
use League\Uri\Components\HierarchicalPath;
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
use League\Uri\Interfaces\Schemes\Uri;
use League\Uri\UriParser;

/**
 * Value object representing a Hierarchical URI.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 *
 * @property-read SchemeInterface           $scheme
 * @property-read UserInfoInterface         $userInfo
 * @property-read HostInterface             $host
 * @property-read PortInterface             $port
 * @property-read HierarchicalPathInterface $path
 * @property-read QueryInterface            $query
 * @property-read FragmentInterface         $fragment
 */
abstract class AbstractHierarchicalUri extends AbstractUri implements HierarchicalUri
{
    use PathModifierTrait;

    /**
     * Create a new instance of URI
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
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromComponents(array $components)
    {
        $components = (new UriParser())->normalizeUriComponents($components);

        return new static(
            new Scheme($components['scheme']),
            new UserInfo($components['user'], $components['pass']),
            new Host($components['host']),
            new Port($components['port']),
            new HierarchicalPath($components['path']),
            new Query($components['query']),
            new Fragment($components['fragment'])
        );
    }

    /**
     * Tell whether the Hierarchical URI is valid
     *
     * @throws \InvalidArgumentException If the Scheme is not supported
     *
     * @return bool
     */
    protected function isValidHierarchicalUri()
    {
        $this->assertSupportedScheme();

        $pos = strpos($this->getSchemeSpecificPart(), '//');
        if (!$this->scheme->isEmpty() && 0 !== $pos) {
            return false;
        }

        return !($this->host->isEmpty() && 0 === $pos);
    }

    /**
     * Assert whether the current scheme is supported by the URI object
     *
     * @throws \InvalidArgumentException If the Scheme is not supported
     */
    protected function assertSupportedScheme()
    {
        $scheme = $this->scheme->__toString();
        if (!empty($scheme) && !isset(static::$supportedSchemes[$scheme])) {
            throw new InvalidArgumentException('The submitted scheme is unsupported by '.get_class($this));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function relativize(Uri $relative)
    {
        if (!$relative instanceof HierarchicalUri) {
            return $relative;
        }

        if ($this->getScheme() !== $relative->getScheme()
            || $this->getAuthority() !== $relative->getAuthority()
        ) {
            return $relative;
        }

        $relativePath = $this->path->modify($relative->getPath());

        return $relative
                ->withScheme('')->withUserInfo('')->withHost('')->withPort('')
                ->withPath($this->path->relativize($relativePath));
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
}
