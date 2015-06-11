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
     * Returns true if the URL is considered empty
     *
     * @return bool
     */
    public function isEmpty();

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
     * @param Url|UriInterface|string $rel the relative URL
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
     * @param mixed $query the data to be merged query can be
     *                     - another Interfaces\Query object
     *                     - a Traversable object
     *                     - an array
     *                     - a string or a Stringable object
     *
     * @return static
     */
    public function mergeQuery($query);

    /**
     * Return an URL without the specified query offsets
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the specidied query data
     *
     * @param callable|array $offsets the list of offsets to remove from the query
     *                                if a callable is given it should filter the list
     *                                of offsets to remove from the query values
     *
     * @return static
     */
    public function withoutQueryValues($offsets);

    /**
     * Return an URL with the filtered query values
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered query data
     *
     * @param callable $callable the callable should filter the list
     *                           of keys to remain in the query
     *
     * @param int      $flag     Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterQuery(callable $callable, $flag = Collection::FILTER_USE_VALUE);

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
    public function appendPath($path);

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
    public function prependPath($path);

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
     * @param callable|array $offsets the list of offsets to remove from the Path
     *                                if a callable is given it should filter the list
     *                                of offsets to remove from the Path
     *
     * @return static
     */
    public function withoutSegments($offsets);

    /**
     * Return an URL without dot segments accordinf to RFC3986 algorithm
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
     * Return an URL with the filtered path segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered segments
     *
     * @param callable $callable the callable should filter the list
     *                           of segment to remain in the path
     *
     * @param int      $flag     Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterPath(callable $callable, $flag = Collection::FILTER_USE_VALUE);

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
    public function appendHost($host);

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
    public function prependHost($host);

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
     * Return an URL with the filtered host label
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered labels
     *
     * @param callable $callable the callable should filter the list
     *                           of label to remain in the host
     *
     * @param int      $flag     Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterHost(callable $callable, $flag = Collection::FILTER_USE_VALUE);
}
