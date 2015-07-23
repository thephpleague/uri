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
namespace League\Uri\Uri;

use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use Psr\Http\Message\UriInterface;

/**
 * a Trait to access URI properties methods
 *
 * @package League.uri
 * @since   1.0.0
 *
 */
trait Properties
{
    /*
     * a Factory to create new URI instances
     */
    use Factory;

    /*
     * partially modifying an URI object
     */
    use PartialModifier;

    /**
     * @return string
     */
    abstract public function __toString();

    /**
     * {@inheritdoc}
     */
    abstract public function getAuthority();

    /**
     * {@inheritdoc}
     */
    abstract public function hasStandardPort();

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
    public function sameValueAs(UriInterface $uri)
    {
        try {
            return static::createFromComponents(static::parse($uri->__toString()))
                ->toAscii()->withoutDotSegments()->ksortQuery()->__toString() === $this
                ->toAscii()->withoutDotSegments()->ksortQuery()->__toString();
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function relativize(Interfaces\Uri $relative)
    {
        if (!$this->scheme->sameValueAs($relative->scheme) || $this->getAuthority() !== $relative->getAuthority()) {
            return $relative;
        }

        return $relative
                ->withScheme('')->withUserInfo('')->withHost('')->withPort('')
                ->withPath($this->path->relativize($relative->path)->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Interfaces\Uri $relative)
    {
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
     * @param Interfaces\Uri $relative the relative URI
     *
     * @return Interfaces\Uri
     */
    protected function resolveAuthority(Interfaces\Uri $relative)
    {
        try {
            return $relative->withScheme($this->scheme);
        } catch (InvalidArgumentException $e) {
            return $relative;
        }
    }

    /**
     * returns the resolve URI
     *
     * @param Interfaces\Uri $relative the relative URI
     *
     * @return Interfaces\Uri
     */
    protected function resolveRelative(Interfaces\Uri $relative)
    {
        $newUri = $this->withProperty('fragment', $relative->fragment->__toString());
        if (!$relative->path->isEmpty()) {
            return $newUri
                ->withProperty('path', $this->resolvePath($newUri, $relative)->__toString())
                ->withProperty('query', $relative->query->__toString());
        }

        if (!$relative->query->isEmpty()) {
            return $newUri->withProperty('query', $relative->query->__toString());
        }

        return $newUri;
    }

    /**
     * returns the resolve URI components
     *
     * @param Interfaces\Uri $newUri   the final URI
     * @param Interfaces\Uri $relative the relative URI
     *
     * @return Interfaces\Path
     */
    protected function resolvePath(Interfaces\Uri $newUri, Interfaces\Uri $relative)
    {
        $path = $relative->path;
        if ($path->isAbsolute()) {
            return $path;
        }

        $segments = $newUri->path->toArray();
        array_pop($segments);
        $isAbsolute = Uri\Path::IS_RELATIVE;
        if ($newUri->path->isEmpty() || $newUri->path->isAbsolute()) {
            $isAbsolute = Uri\Path::IS_ABSOLUTE;
        }

        return Uri\Path::createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
    }
}
