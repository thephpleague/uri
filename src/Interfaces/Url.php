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
namespace League\Url\Interfaces;

use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URL.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Url extends UriInterface
{
    /**
     * Return an array representation of the Url
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns whether two UriInterface represents the same value
     * The Comparaison is based on the __toString method.
     * No normalization is done
     *
     * @param UriInterface $url
     *
     * @return bool
     */
    public function sameValueAs(UriInterface $url);

    /**
     * Returns whether a Url is absolute or relative. An Url is
     * said to be absolute if is has:
     * - a non empty scheme.
     * - an authority part
     *
     * @return bool
     */
    public function isAbsolute();

    /**
     * Returns whether the standard port for the given scheme is used, when
     * the scheme is unknown or unsupported will the method return false
     *
     * @return bool
     */
    public function hasStandardPort();

    /**
     * Returns an instance resolve according to a given URL
     *
     * This method MUST retain the state of the current instance, and return
     * an instance resolved according to supplied URL
     *
     * @param string $rel the relative URL
     *
     * @return static
     */
    public function resolve($rel);

    /**
     * Return an URL with update query values
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query data
     *
     * @param Traversable|array $query the data to be merged
     *
     * @return static
     */
    public function mergeQueryParameters($query);

    /**
     * Return an URL without the submitted query parameters
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the specidied query data
     *
     * @param callable|array $query the list of parameter to remove from the query
     *                              if a callable is given it should filter the list
     *                              of parameter to remove from the query
     *
     * @return static
     */
    public function withoutQueryParameters($query);

    /**
     * Return an URL with filtered query parameters
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered query data
     *
     * @param callable|array $query the list of parameter to remove from the query
     *                              if a callable is given it should filter the list
     *                              of segment to remain in the query
     *
     * @param int            $flag    Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterQuery($callable, $flag = Url\Interfaces\Collection::FILTER_USE_VALUE);

    /**
     * Return an URL with its path appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the appended path
     *
     * @param CollectionComponent|string $path the data to append
     *
     * @return static
     */
    public function appendSegments($path);

    /**
     * Return an URL with its path prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the prepended path
     *
     * @param CollectionComponent|string $path the data to prepend
     *
     * @return static
     */
    public function prependSegments($path);

    /**
     * Return an URL with one of its Path segment replaced
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the path
     *
     * @param int                        $offset the Path segment offset
     * @param CollectionComponent|string $value  the data to inject
     *
     * @return static
     */
    public function replaceSegment($offset, $value);

    /**
     * Return an URL without the submitted path segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the specified segments
     *
     * @param callable|array $offsets the list of segments offset to remove from the Path
     *                                if a callable is given it should filter the list
     *                                of offset to remove from the Path
     *
     * @return static
     */
    public function withoutSegments($offsets);

    /**
     * Return an URL without dot segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without dot segment according to RFC3986 algorithm
     *
     * @return static
     */
    public function withoutDotSegments();

    /**
     * Return an URL without internal empty segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without adjacent segment delimiters
     *
     * @return static
     */
    public function withoutEmptySegments();

    /**
     * Return an URL without the submitted path segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered segments
     *
     * @param callable|array $query the list of parameter to remove from the path
     *                              if a callable is given it should filter the list
     *                              of segment to remain in the path
     *
     * @param int            $flag    Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterSegments($callable, $flag = Url\Interfaces\Collection::FILTER_USE_VALUE);

    /**
     * Return an URL with the path extension updated
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the modified path extension
     *
     * @param string $extension the new path extension
     *
     * @return static
     */
    public function withExtension($extension);

    /**
     * Return an URL with the Host appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified host with the appended labels
     *
     * @param CollectionComponent|string $host the data to append
     *
     * @return static
     */
    public function appendLabels($host);

    /**
     * Return an URL with the Host prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified host with the prepended labels
     *
     * @param CollectionComponent|string $host the data to prepend
     *
     * @return static
     */
    public function prependLabels($host);

    /**
     * Return an URL with one of its Host label replaced
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified host with the replaced labels
     *
     * @param int                        $offset the Host label offset
     * @param CollectionComponent|string $value  the data to inject
     *
     * @return static
     */
    public function replaceLabel($offset, $value);

    /**
     * Return an URL without the submitted host labels
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the modified host without the selected labels
     *
     * @param callable|array $offsets the list of label offsets to remove from the Host
     *                                if a callable is given it should filter the list
     *                                of offset to remove from the Host
     *
     * @return static
     */
    public function withoutLabels($offsets);

    /**
     * Return an URL without the filtered host labels
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered labels
     *
     * @param callable|array $query the list of label to select from the host
     *                              if a callable is given it should filter the list
     *                              of label to remain in the host
     *
     * @param int            $flag    Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterLabels($callable, $flag = Url\Interfaces\Collection::FILTER_USE_VALUE);
}
