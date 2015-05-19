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

/**
 * Value object representing a URL Path component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Path extends CollectionComponent
{
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
    public function withoutDuplicateDelimiters();

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
     * Format Path according to PSR-7 output
     *
     * @param  string $auth the authority string
     *
     * @return string
     */
    public function format($auth);
}
