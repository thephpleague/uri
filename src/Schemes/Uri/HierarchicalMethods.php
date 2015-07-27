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
namespace League\Uri\Schemes\Uri;

use Exception;
use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use Psr\Http\Message\UriInterface;
use ReflectionClass;

/**
 * a Trait to access URI properties methods
 *
 * @package League.uri
 * @since   1.0.0
 *
 */
trait HierarchicalMethods
{
    /*
     * a trait to partially modify an HierarchicalUri object
     */
    use HierarchicalModifier;

    /*
     * URI complementary methods
     */
    use Properties;

    /**
     * Supported Schemes
     *
     * @var array
     */
    protected static $supportedSchemes = [];

    /**
     * {@inheritdoc}
     */
    abstract public function getAuthority();

    /**
     * {@inheritdoc}
     */
    public function isOpaque()
    {
        return false;
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

    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents(static::parse($uri));
    }

    /**
     * Create a new instance from an array returned by
     * PHP parse_url function
     *
     * @param array $components
     *
     * @return Uri\Schemes\HierarchicalUri
     */
    public static function createFromComponents(array $components)
    {
        $components = static::formatComponents($components);

        return (new ReflectionClass(get_called_class()))->newInstance(
            new Uri\Scheme($components['scheme']),
            new Uri\UserInfo($components['user'], $components['pass']),
            new Uri\Host($components['host']),
            new Uri\Port($components['port']),
            new Uri\Path($components['path']),
            new Uri\Query($components['query']),
            new Uri\Fragment($components['fragment'])
        );
    }
}
