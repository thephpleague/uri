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

use League\Uri;
use League\Uri\Interfaces;

/**
 * a Trait to proxy partial update of a League\Uri\Uri object
 *
 * @package League.url
 * @since   4.0.0
 */
trait Modifier
{
    /**
     * Scheme Component
     *
     * @var Interfaces\Scheme
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var Interfaces\UserInfo
     */
    protected $userInfo;

    /**
     * Host Component
     *
     * @var Interfaces\Host
     */
    protected $host;

    /**
     * Port Component
     *
     * @var Interfaces\Port
     */
    protected $port;

    /**
     * Path Component
     *
     * @var Interfaces\Path
     */
    protected $path;

    /**
     * Query Component
     *
     * @var Interfaces\Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var Interfaces\Fragment
     */
    protected $fragment;

    /**
     * Trait To get/set immutable value property
     */
    use Uri\Types\ImmutableProperty;

    /**
     * {@inheritdoc}
     */
    abstract public function getAuthority();

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
    public function filterQuery(callable $callable, $flag = Interfaces\Collection::FILTER_USE_VALUE)
    {
        return $this->withProperty('query', $this->query->filter($callable, $flag));
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
    public function filterPath(callable $callable, $flag = Interfaces\Collection::FILTER_USE_VALUE)
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
    public function withoutDotSegments()
    {
        return $this->withProperty('path', $this->path->withoutDotSegments());
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
    public function filterHost(callable $callable, $flag = Interfaces\Collection::FILTER_USE_VALUE)
    {
        return $this->withProperty('host', $this->host->filter($callable, $flag));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($url)
    {
        $relative = $this->convertToUrlObject($url);
        if ($relative->isAbsolute()) {
            return $relative->withoutDotSegments();
        }

        if (!$relative->host->isEmpty() && $relative->getAuthority() != $this->getAuthority()) {
            return $relative->withScheme($this->scheme)->withoutDotSegments();
        }

        return $this->resolveRelative($relative)->withoutDotSegments();
    }

    /**
     * Convert to an Url object
     *
     * @param  Interfaces\Uri|string $url
     *
     * @return Interfaces\Uri
     */
    protected function convertToUrlObject($url)
    {
        if ($url instanceof Interfaces\Uri) {
            return $url;
        }

        return Uri\Uri::createFromString($url, $this->scheme->getSchemeRegistry());
    }

    /**
     * returns the resolve URL
     *
     * @param Interfaces\Uri $relative the relative URL
     *
     * @return static
     */
    protected function resolveRelative(Interfaces\Uri $relative)
    {
        $newUrl = $this->withProperty('fragment', $relative->fragment);
        if (!$relative->path->isEmpty()) {
            return $newUrl
                ->withProperty('path', $this->resolvePath($newUrl, $relative))
                ->withProperty('query', $relative->query);
        }

        if (!$relative->query->isEmpty()) {
            return $newUrl->withProperty('query', $relative->query);
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
        if (!$path->isAbsolute()) {
            $segments = $newUrl->path->toArray();
            array_pop($segments);
            $isAbsolute = Uri\Path::IS_RELATIVE;
            if ($newUrl->path->isEmpty() || $newUrl->path->isAbsolute()) {
                $isAbsolute = Uri\Path::IS_ABSOLUTE;
            }
            $path = Uri\Path::createFromArray(array_merge($segments, $path->toArray()), $isAbsolute);
        }

        return $path;
    }
}
