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
 * a trait to add More modifying methods to League\Url\Url
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
     * Return an URL with update query values
     *
     * @param Traversable|array $query the data to be merged
     *
     * @return static
     */
    public function mergeQueryParameters($query)
    {
        return $this->withProperty('query', $this->query->merge($query));
    }

    /**
     * Return an URL without the submitted query parameters
     *
     * @param callable|array $query the list of parameter to remove from the query
     *                              if a callable is given it should filter the list
     *                              of parameter to remove from the query
     *
     * @return static
     */
    public function withoutQueryParameters($query)
    {
        return $this->withProperty('query', $this->query->without($query));
    }

    /**
     * Return an URL without the filtered query parameters
     *
     * @param callable $callable a callable which filter the query parameters
     *                           according to their content
     *
     * @return static
     */
    public function filterQueryValues(callable $callable)
    {
        return $this->withProperty('query', $this->query->filter($callable));
    }

    /**
     * Return an URL with its path appended
     *
     * @param \League\Url\Interfaces\CollectionComponent|string $path the data to append
     *
     * @return static
     */
    public function appendSegments($path)
    {
        return $this->withProperty('path', $this->path->append($path));
    }

    /**
     * Return an URL with its path prepended
     *
     * @param \League\Url\Interfaces\CollectionComponent|string $path the data to prepend
     *
     * @return static
     */
    public function prependSegments($path)
    {
        return $this->withProperty('path', $this->path->prepend($path));
    }

    /**
     * Return an URL with one of its Path segment replaced
     *
     * @param int                                               $offset the Path segment offset
     * @param \League\Url\Interfaces\CollectionComponent|string $value   the data to inject
     *
     * @return static
     */
    public function replaceSegment($offset, $value)
    {
        return $this->withProperty('path', $this->path->replace($offset, $value));
    }

    /**
     * Return an URL without the submitted path segments
     *
     * @param callable|array $offsets the list of segments offset to remove from the Path
     *                                if a callable is given it should filter the list
     *                                of offset to remove from the Path
     *
     * @return static
     */
    public function withoutSegments($offsets)
    {
        return $this->withProperty('path', $this->path->without($offsets));
    }

    /**
     * Return an URL without dot segments
     *
     * @return static
     */
    public function withoutDotSegments()
    {
        return $this->withProperty('path', $this->path->withoutDotSegments());
    }

    /**
     * Return an URL without internal empty segments
     *
     * @return static
     */
    public function withoutEmptySegments()
    {
        return $this->withProperty('path', $this->path->withoutEmptySegments());
    }

    /**
     * Return an URL without the submitted path segments
     *
     * @param callable $callable a callable which filter the path segment
     *                           according to the segment content
     *
     * @return static
     */
    public function filterSegments(callable $callable)
    {
        return $this->withProperty('path', $this->path->filter($callable));
    }

    /**
     * Return an URL with the path extension updated
     *
     * @param  string $extension the new path extension
     *
     * @return static
     */
    public function withExtension($extension)
    {
        return $this->withProperty('path', $this->path->withExtension($extension));
    }

    /**
     * Return an URL with the Host appended
     *
     * @param \League\Url\Interfaces\CollectionComponent|string $host the data to append
     *
     * @return static
     */
    public function appendLabels($host)
    {
        return $this->withProperty('host', $this->host->append($host));
    }

    /**
     * Return an URL with the Host prepended
     *
     * @param \League\Url\Interfaces\CollectionComponent|string $host the data to prepend
     *
     * @return static
     */
    public function prependLabels($host)
    {
        return $this->withProperty('host', $this->host->prepend($host));
    }

    /**
     * Return an URL with one of its Host label replaced
     *
     * @param int                                               $offset the Host label offset
     * @param \League\Url\Interfaces\CollectionComponent|string $value  the data to inject
     *
     * @return static
     */
    public function replaceLabel($offset, $value)
    {
        return $this->withProperty('host', $this->host->replace($offset, $value));
    }

    /**
     * Return an URL without the submitted host labels
     *
     * @param callable|array $offsets the list of label offsets to remove from the Host
     *                                if a callable is given it should filter the list
     *                                of offset to remove from the Host
     *
     * @return static
     */
    public function withoutLabels($offsets)
    {
        return $this->withProperty('host', $this->host->without($offsets));
    }

    /**
     * Return an URL without the filtered host labels
     *
     * @param callable $callable a callable which filter the host labels
     *                           according to their content
     *
     * @return static
     */
    public function filterLabels(callable $callable)
    {
        return $this->withProperty('host', $this->host->filter($callable));
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
                ->withPath($this->resolvePath($newUrl, $relative))
                ->withQuery($relative->query);
        }

        if (! $relative->query->isEmpty()) {
            return $newUrl->withQuery($relative->query);
        }

        return $newUrl;
    }

    /**
     * returns the resolve URL components
     *
     * @param Url\Url $newUrl   the final URL
     * @param Url\Url $relative the relative URL
     *
     * @return Path
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
