<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri\Uri;

use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use Psr\Http\Message\UriInterface;

/**
 * a Trait to access URI properties methods
 *
 * @package League.url
 * @since   1.0.0
 *
 */
trait Properties
{
    /**
     * a Factory to create new URI instances
     */
    use Factory;

    /**
     * partially modifying an URL object
     */
    use PartialModifier;

    /**
     * SchemeRegistry Component
     *
     * @var Interfaces\SchemeRegistry
     */
    protected $schemeRegistry;

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
    public function sameValueAs(UriInterface $url)
    {
        try {
            return static::createFromComponents($this->schemeRegistry, static::Parse($url->__toString()))
                ->toAscii()->normalize()->ksortQuery()->__toString() === $this
                ->toAscii()->normalize()->ksortQuery()->__toString();
        } catch (InvalidArgumentException $e) {
            return false;
        }
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

        return $this->schemeRegistry->getPort($this->scheme)->sameValueAs($this->port);
    }

    /**
     * {@inheritdoc}
     */
    public function withSchemeRegistry(Interfaces\SchemeRegistry $schemeRegistry)
    {
        if (!$this->isSchemeRegistered($this->scheme, $schemeRegistry)) {
            throw new InvalidArgumentException('The submitted registry does not support the current scheme');
        }

        if ($this->schemeRegistry->sameValueAs($schemeRegistry)) {
            return $this;
        }

        $clone = clone $this;
        $clone->schemeRegistry = $schemeRegistry;
        return $clone;
    }

    /**
     * Return whether the scheme is supported by the Scheme registry
     *
     * @param  Interfaces\Scheme         $scheme
     * @param  Interfaces\SchemeRegistry $schemeRegistry
     *
     * @return bool
     */
    protected function isSchemeRegistered(Interfaces\Scheme $scheme, Interfaces\SchemeRegistry $schemeRegistry)
    {
        return $scheme->isEmpty() || $schemeRegistry->hasKey($scheme);
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
        if (!$relative->scheme->isEmpty()) {
            return $relative->normalize();
        }

        if (!$relative->host->isEmpty()) {
            return $this->resolveAuthority($relative)->normalize();
        }

        return $this->resolveRelative($relative)->normalize();
    }

    /**
     * returns the resolve URL according to the authority
     *
     * @param Interfaces\Uri $relative the relative URL
     *
     * @return Interfaces\Uri
     */
    protected function resolveAuthority(Interfaces\Uri $relative)
    {
        if (!$relative->schemeRegistry->hasKey($this->scheme)) {
            $schemeRegistry = $relative->schemeRegistry->merge([
                $this->scheme->__toString() => $this->schemeRegistry->getPort($this->scheme),
            ]);
            $relative = $relative->withSchemeRegistry($schemeRegistry);
        }

        return $relative->withScheme($this->scheme);
    }

    /**
     * returns the resolve URL
     *
     * @param Interfaces\Uri $relative the relative URL
     *
     * @return Interfaces\Uri
     */
    protected function resolveRelative(Interfaces\Uri $relative)
    {
        $newUrl = $this->withProperty('fragment', $relative->fragment->__toString());
        if (!$relative->path->isEmpty()) {
            return $newUrl
                ->withProperty('path', $this->resolvePath($newUrl, $relative)->__toString())
                ->withProperty('query', $relative->query->__toString());
        }

        if (!$relative->query->isEmpty()) {
            return $newUrl->withProperty('query', $relative->query->__toString());
        }

        return $newUrl;
    }

    /**
     * returns the resolve URL components
     *
     * @param Interfaces\Uri $newUrl   the final URL
     * @param Interfaces\Uri $relative the relative URL
     *
     * @return Interfaces\Path
     */
    protected function resolvePath(Interfaces\Uri $newUrl, Interfaces\Uri $relative)
    {
        $path = $relative->path;
        if ($path->isAbsolute()) {
            return $path;
        }

        $segments = $newUrl->path->toArray();
        array_pop($segments);
        $isAbsolute = Uri\Path::IS_RELATIVE;
        if ($newUrl->path->isEmpty() || $newUrl->path->isAbsolute()) {
            $isAbsolute = Uri\Path::IS_ABSOLUTE;
        }

        return Uri\Path::createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
    }
}
