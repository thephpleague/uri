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
namespace League\Url\Services;

use League\Url\Url;

/**
 * an URL Builder to ease URL manipulation
 *
 * @package League.url
 * @since 4.0.0
 */
class Builder
{
    /**
     * The Url
     *
     * @var Url
     */
    protected $url;

    /**
     * Create a new instance
     *
     * @param Url|string $url
     */
    public function __construct($url = null)
    {
        $this->setUrl($url);
    }

    /**
     * Return an instance of Builder
     *
     * @param Url $url
     *
     * @return static
     */
    protected function newInstance(Url $url)
    {
        if (! $this->url->sameValueAs($url)) {
            $this->url = $url;
        }
        return $this;
    }

    /**
     * Set The URL the builder will use
     * 
     * @param Url|string $url
     * 
     * @return static
     */
    public function setUrl($url)
    {
        if (! $url instanceof Url) {
            $url = Url::createFromUrl($url);
        }
        $this->url = $url;
        
        return $this;
    }

    /**
     * Return the original URL instance
     *
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Return an URL with update query values
     *
     * @param Traversable|array $query the data to be merged
     *
     * @return static
     */
    public function mergeQueryValues($query)
    {
        return $this->newInstance($this->url->withQuery($this->url->query->merge($query)));
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
    public function withoutQueryValues($query)
    {
        return $this->newInstance($this->url->withQuery($this->url->query->without($query)));
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
        return $this->newInstance($this->url->withQuery($this->url->query->filter($callable)));
    }

    /**
     * Return an URL with its path appended
     *
     * @param Interfaces\CollectionComponent|string $path the data to append
     *
     * @return static
     */
    public function appendSegments($path)
    {
        return $this->newInstance($this->url->withPath($this->url->path->append($path)));
    }

    /**
     * Return an URL with its path prepended
     *
     * @param Interfaces\CollectionComponent|string $path the data to prepend
     *
     * @return static
     */
    public function prependSegments($path)
    {
        return $this->newInstance($this->url->withPath($this->url->path->prepend($path)));
    }

    /**
     * Return an URL with one of its Path segment replaced
     *
     * @param Interfaces\CollectionComponent|string $path   the data to inject
     * @param int                                   $offset the Path segment offset
     *
     * @return static
     */
    public function replaceSegment($path, $offset)
    {
        return $this->newInstance($this->url->withPath($this->url->path->replace($path, $offset)));
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
        return $this->newInstance($this->url->withPath($this->url->path->without($offsets)));
    }

    /**
     * Return an URL without internal empty segments
     *
     * @return static
     */
    public function withoutDuplicateSegments()
    {
        return $this->newInstance($this->url->withPath($this->url->path->withoutDuplicateDelimiters()));
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
        return $this->newInstance($this->url->withPath($this->url->path->filter($callable)));
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
        return $this->newInstance($this->url->withPath($this->url->path->withExtension($extension)));
    }

    /**
     * Return an URL with the Host appended
     *
     * @param Interfaces\CollectionComponent|string $host the data to append
     *
     * @return static
     */
    public function appendLabels($host)
    {
        return $this->newInstance($this->url->withHost($this->url->host->append($host)));
    }

    /**
     * Return an URL with the Host prepended
     *
     * @param Interfaces\CollectionComponent|string $host the data to prepend
     *
     * @return static
     */
    public function prependLabels($host)
    {
        return $this->newInstance($this->url->withHost($this->url->host->prepend($host)));
    }

    /**
     * Return an URL with one of its Host label replaced
     *
     * @param Interfaces\CollectionComponent|string $host   the data to inject
     * @param int                                   $offset the Host label offset
     *
     * @return static
     */
    public function replaceLabel($host, $offset)
    {
        return $this->newInstance($this->url->withHost($this->url->host->replace($host, $offset)));
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
        return $this->newInstance($this->url->withHost($this->url->host->without($offsets)));
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
        return $this->newInstance($this->url->withHost($this->url->host->filter($callable)));
    }
}
