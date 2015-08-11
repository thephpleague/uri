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
namespace League\Uri\Interfaces\Schemes;

use League\Uri\Interfaces\Components\Collection;
use League\Uri\Interfaces\Components\HierarchicalComponent;

/**
 * Value object representing a URI.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.uri
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986
 */
interface HierarchicalUri extends Uri
{
    /**
     * Returns whether the standard port for the given scheme is used, when
     * the scheme is unknown or unsupported will the method return false
     *
     * @return bool
     */
    public function hasStandardPort();

    /**
     * Returns an instance relativized according to a given URI
     *
     * This method MUST retain the state of the current instance, and return
     * an instance resolved according to supplied URI
     *
     * @param Uri $rel the relative URI
     *
     * @return static
     */
    public function relativize(Uri $rel);

    /**
     * Return an instance with its path appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the appended path
     *
     * @param HierarchicalComponent|string $path the data to append
     *
     * @return static
     */
    public function appendPath($path);

    /**
     * Return an instance with its path prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the prepended path
     *
     * @param HierarchicalComponent|string $path the data to prepend
     *
     * @return static
     */
    public function prependPath($path);

    /**
     * Return an instance with one of its Path segment replaced
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the path
     *
     * @param int                          $key   the Path segment offset
     * @param HierarchicalComponent|string $value the data to inject
     *
     * @return static
     */
    public function replaceSegment($key, $value);

    /**
     * Return an instance without the submitted path segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the specified segments
     *
     * @param callable|array $keys the list of offsets to remove from the Path
     *                             if a callable is given it should filter the list
     *                             of offsets to remove from the Path
     *
     * @return static
     */
    public function withoutSegments($keys);

    /**
     * Return an instance without internal empty segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without adjacent segment delimiters
     *
     * @return static
     */
    public function withoutEmptySegments();

    /**
     * Return an instance with the filtered path segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered segments
     *
     * @param callable $callable the callable should filter the list
     *                           of segment to remain in the path
     *
     * @param int $flag Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterPath(callable $callable, $flag = Collection::FILTER_USE_VALUE);

    /**
     * Return an instance with the path extension updated
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
     * Return an instance with the path containing a trailing slash
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the path component with a trailing slash
     *
     * if the path is an empty rootless path no slash is added
     *
     * @return static
     */
    public function withTrailingSlash();

    /**
     * Returns an instance without a trailing slash
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the path component without a trailing slash
     *
     * if the Path is the root path no slash is removed
     *
     * @return static
     */
    public function withoutTrailingSlash();
}
