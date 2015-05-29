<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url\Utilities;

use League\Url;

/**
 * a Trait to ease partial update of a League\Url\Url object
 *
 * @package League.url
 * @since 4.0.0
 */
trait UrlModifier
{
    /**
     * Scheme Component
     *
     * @var \League\Url\Interfaces\Scheme
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var \League\Url\Interfaces\UserInfo
     */
    protected $userInfo;

    /**
     * Host Component
     *
     * @var \League\Url\Interfaces\Host
     */
    protected $host;

    /**
     * Port Component
     *
     * @var \League\Url\Interfaces\Port
     */
    protected $port;

    /**
     * Path Component
     *
     * @var \League\Url\Interfaces\Path
     */
    protected $path;

    /**
     * Query Component
     *
     * @var \League\Url\Interfaces\Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var \League\Url\Fragment
     */
    protected $fragment;

    /**
     * Trait To get/set immutable value property
     */
    use ImmutableProperty;

    /**
     * {@inheritdoc}
     */
    abstract public function getAuthority();

    /**
     * {@inheritdoc}
     */
    public function mergeQueryParameters($query)
    {
        return $this->withProperty('query', $this->query->merge($query));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutQueryParameters($query)
    {
        return $this->withProperty('query', $this->query->without($query));
    }

    /**
     * {@inheritdoc}
     */
    public function filterQuery($callable, $flag = Url\Interfaces\Collection::FILTER_USE_VALUE)
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
    public function filterPath($callable, $flag = Url\Interfaces\Collection::FILTER_USE_VALUE)
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
    public function filterHost($callable, $flag = Url\Interfaces\Collection::FILTER_USE_VALUE)
    {
        return $this->withProperty('host', $this->host->filter($callable, $flag));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($url)
    {
        $relative = Url\Url::createFromUrl($url);
        if ($relative->isAbsolute()) {
            return $relative->withoutDotSegments();
        }

        if (! $relative->host->isEmpty() && $relative->getAuthority() != $this->getAuthority()) {
            return $relative->withScheme($this->scheme)->withoutDotSegments();
        }

        return $this->resolveRelative($relative)->withoutDotSegments();
    }

    /**
     * returns the resolve URL
     *
     * @param Url\Url $relative the relative URL
     *
     * @return static
     */
    protected function resolveRelative(Url\Url $relative)
    {
        $newUrl = $this->withProperty('fragment', $relative->fragment);
        if (! $relative->path->isEmpty()) {
            return $newUrl
                ->withProperty('path', $this->resolvePath($newUrl, $relative))
                ->withProperty('query', $relative->query);
        }

        if (! $relative->query->isEmpty()) {
            return $newUrl->withProperty('query', $relative->query);
        }

        return $newUrl;
    }

    /**
     * returns the resolve URL components
     *
     * @param Url\Url $newUrl   the final URL
     * @param Url\Url $relative the relative URL
     *
     * @return Url\Interfaces\Path
     */
    protected function resolvePath(Url\Url $newUrl, Url\Url $relative)
    {
        $path = $relative->path;
        if (! $path->isAbsolute()) {
            $segments = $newUrl->path->toArray();
            array_pop($segments);
            $is_absolute = Url\Path::IS_RELATIVE;
            if ($newUrl->path->isEmpty() || $newUrl->path->isAbsolute()) {
                $is_absolute = Url\Path::IS_ABSOLUTE;
            }
            $path = Url\Path::createFromArray(array_merge($segments, $path->toArray()), $is_absolute);
        }

        return $path;
    }
}
