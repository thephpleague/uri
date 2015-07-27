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

use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;

/**
 * a Trait to access URI properties methods
 *
 * @package League.uri
 * @since   1.0.0
 *
 */
trait Properties
{
    /**
     * {@inheritdoc}
     */
    abstract public function getScheme();

    /**
     * {@inheritdoc}
     */
    abstract public function getSchemeSpecificPart();

    /**
     * {@inheritdoc}
     */
    abstract protected function withProperty($property, $value);

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getScheme() . ':' . $this->getSchemeSpecificPart();
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
        $isAbsolute = Uri\Path::IS_RELATIVE;
        if ($newUri->path->isEmpty() || $newUri->path->isAbsolute()) {
            $isAbsolute = Uri\Path::IS_ABSOLUTE;
        }

        return Uri\Path::createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
    }

    /**
     * Format the components to works with all the constructors
     *
     * @param array $components
     *
     * @return array
     */
    protected static function formatComponents(array $components)
    {
        foreach ($components as $name => $value) {
            $components[$name] = (null === $value && 'port' != $name) ? '' : $value;
        }

        return array_merge([
            'scheme' => '', 'user'     => '',
            'pass'   => '', 'host'     => '',
            'port'   => null, 'path'   => '',
            'query'  => '', 'fragment' => '',
        ], $components);
    }


    /**
     * Parse a string as an URI
     *
     * Parse an URI string using PHP parse_url while applying bug fixes
     * and taking into account UTF-8
     *
     * Taken from php.net manual comments:
     *
     * @see http://php.net/manual/en/function.parse-url.php#114817
     *
     * @param string $uri The URI to parse
     *
     * @throws InvalidArgumentException if the URI can not be parsed
     *
     * @return array
     */
    public static function parse($uri)
    {
        $pattern = '%([a-zA-Z][a-zA-Z0-9+\-.]*)?(:?//)?([^:/@?&=#\[\]]+)%usD';
        $enc_uri = preg_replace_callback($pattern, function ($matches) {
            return sprintf('%s%s%s', $matches[1], $matches[2], rawurlencode($matches[3]));
        }, (string) $uri);

        $components = @parse_url($enc_uri);
        if (is_array($components)) {
            return static::formatParsedComponents($components);
        }

        $components = @parse_url(static::fixUrlScheme($enc_uri));
        if (is_array($components)) {
            unset($components['scheme']);

            return static::formatParsedComponents($components);
        }

        throw new InvalidArgumentException(sprintf('The given URI: `%s` could not be parse', (string) $uri));
    }

    /**
     * Format and Decode UTF-8 components
     *
     * @param array $components
     *
     * @return array
     */
    protected static function formatParsedComponents(array $components)
    {
        $components = array_merge([
            'scheme' => null, 'user'     => null,
            'pass'   => null, 'host'     => null,
            'port'   => null, 'path'     => null,
            'query'  => null, 'fragment' => null,
        ], array_map('rawurldecode', $components));

        if (null !== $components['port']) {
            $components['port'] = (int) $components['port'];
        }

        return $components;
    }

    /**
     * bug fix for unpatched PHP version
     *
     * in the following versions
     *    - PHP 5.4.7 => 5.5.24
     *    - PHP 5.6.0 => 5.6.8
     *    - HHVM all versions
     *
     * We must prepend a temporary missing scheme to allow
     * parsing with parse_url function
     *
     * @see https://bugs.php.net/bug.php?id=68917
     *
     * @param string $uri The URI to parse
     *
     * @return string
     */
    protected static function fixUrlScheme($uri)
    {
        static $is_bugged;

        if (is_null($is_bugged)) {
            $is_bugged = !is_array(@parse_url('//a:1'));
        }

        if (!$is_bugged || strpos($uri, '/') !== 0) {
            throw new InvalidArgumentException(sprintf('The given URI: `%s` could not be parse', $url));
        }

        return 'php-bugfix-scheme:' . $uri;
    }
}
