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

use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use Psr\Http\Message\UriInterface;

/**
 * a Trait to access URI properties methods
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
     * @var Interfaces\Scheme
     */
    protected $scheme;

    /*
     * Trait To get/set immutable value property
     */
    use Uri\Types\ImmutablePropertyTrait;

    /*
     * a trait to add named constructors
     */
    use NamedConstructorsTrait;

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
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        return $this->withProperty('scheme', $scheme);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getSchemeSpecificPart();

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
                'This method requires an object implementing the `League\Uri\Interfaces\Schemes\Uri` interface
                or the PSR-7 `Psr\Http\Message\UriInterface` interface'
            );
        }

        return $uri->__toString() === $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Interfaces\Schemes\Uri $relative)
    {
        $className = get_class($this);
        if (!$relative instanceof Interfaces\Schemes\HierarchicalUri || !$relative instanceof $className) {
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
     * returns the resolve URI according to the authority
     *
     * @param Interfaces\Schemes\HierarchicalUri $relative the relative URI
     *
     * @return Interfaces\Schemes\HierarchicalUri
     */
    protected function resolveAuthority(Interfaces\Schemes\HierarchicalUri $relative)
    {
        return $relative->withScheme($this->scheme);
    }

    /**
     * returns the resolve URI
     *
     * @param Interfaces\Schemes\HierarchicalUri $relative the relative URI
     *
     * @return Interfaces\Schemes\HierarchicalUri
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
     * @return Interfaces\Path
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
        $isAbsolute = Uri\Components\Path::IS_RELATIVE;
        if ($newUri->path->isEmpty() || $newUri->path->isAbsolute()) {
            $isAbsolute = Uri\Components\Path::IS_ABSOLUTE;
        }

        return Uri\Components\Path::createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
    }
}
