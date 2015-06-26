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
namespace League\Url\Interfaces;

/**
 * Value object representing a URL Path component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986#section-3.3
 */
interface Path extends HierarchicalComponent
{
    /**
     * Returns whether or not the path is absolute or relative
     *
     * @return bool
     */
    public function isAbsolute();

    /**
     * Retrieves a single path segment.
     *
     * Retrieves a single path segment. If the segment offset has not been set,
     * returns the default value provided.
     *
     * @param string $offset  the segment offset
     * @param mixed  $default Default value to return if the offset does not exist.
     *
     * @return mixed
     */
    public function getSegment($offset, $default = null);

    /**
     * Returns parent directory's path
     *
     * @return string
     */
    public function getDirname();

    /**
     * Returns the path basename
     *
     * @return string
     */
    public function getBasename();

    /**
     * Returns the basename extension
     *
     * @return string
     */
    public function getExtension();

    /**
     * Returns whether or not the path has a trailing delimiter
     *
     * @return bool
     */
    public function hasTrailingDelimiter();

    /**
     * Returns an instance with the specified basename extension
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the extension basename modified.
     *
     * @param string $ext the new extension
     *                    can preceeded with or without the dot (.) character
     *
     * @throws \LogicException If the basename is empty
     *
     * @return static
     */
    public function withExtension($ext);

    /**
     * Returns an instance without dot segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the path component normalized by removing
     * the dot segment.
     *
     * @return static
     */
    public function withoutDotSegments();

    /**
     * Returns an instance without duplicate delimiters
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the path component normalized by removing
     * multiple consecutive empty segment
     *
     * @return static
     */
    public function withoutEmptySegments();
}
