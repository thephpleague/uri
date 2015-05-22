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

use League\Url\Interfaces;
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
     * Scheme Component
     *
     * @var Interfaces\Url
     */
    protected $url;

    /**
     * Create a new instance of URL
     *
     * @param Interfaces\Url|string $url
     */
    public function __construct($url)
    {
        if (! $url instanceof Interfaces\Url) {
            $url = Url::createFromUrl($url);
        }

        $this->url = $url;
    }

    /**
     * Return an instance of Builder
     *
     * @param Interfaces\Url $url
     *
     * @return static
     */
    protected function newInstance(Interfaces\Url $url)
    {
        if ($url->sameValueAs($this->url)) {
            return $this;
        }
        return new static($url);
    }

    /**
     * Return the original URL instance
     *
     * @return Interfaces\Url
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
        return $this->newInstance($this->url->withQuery($this->url->getPart('query')->merge($query)));
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
        return $this->newInstance($this->url->withQuery($this->url->getPart('query')->without($query)));
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
        return $this->newInstance($this->url->withQuery($this->url->getPart('query')->filter($callable)));
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
        return $this->newInstance($this->url->withPath($this->url->getPart('path')->append($path)));
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
        return $this->newInstance($this->url->withPath($this->url->getPart('path')->prepend($path)));
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
        return $this->newInstance($this->url->withPath($this->url->getPart('path')->replace($path, $offset)));
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
        return $this->newInstance($this->url->withPath($this->url->getPart('path')->without($offsets)));
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
        return $this->newInstance($this->url->withPath($this->url->getPart('path')->filter($callable)));
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
        return $this->newInstance($this->url->withPath($this->url->getPart('path')->withExtension($extension)));
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
        return $this->newInstance($this->url->withHost($this->url->getPart('host')->append($host)));
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
        return $this->newInstance($this->url->withHost($this->url->getPart('host')->prepend($host)));
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
        return $this->newInstance($this->url->withHost($this->url->getPart('host')->replace($host, $offset)));
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
        return $this->newInstance($this->url->withHost($this->url->getPart('host')->without($offsets)));
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
        return $this->newInstance($this->url->withHost($this->url->getPart('host')->filter($callable)));
    }
}
