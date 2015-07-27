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
 *
 */
interface Uri
{
    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     *
     * @return string The URI scheme.
     */
    public function getScheme();

    /**
     * Retrieve the scheme specific part of the URI.
     *
     * If no specific part information is present, this method MUST return an empty
     * string.
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getSchemeSpecificPart();

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     *
     * @return string
     */
    public function __toString();

    /**
     * Return an array representation of the URI
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns true if the URI is considered empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns whether two objects represents the same value
     * The comparison is based on the __toString method.
     * The following normalization is done prior to comparaison
     *
     *  - hosts if present are converted using the punycode algorithm
     *  - paths if present are normalized by removing dot segments
     *  - query strings if present are sorted using their offsets
     *
     * @param Uri|Psr\Http\Message\UriInterface $uri
     *
     * @return bool
     */
    public function sameValueAs($uri);

    /**
     * Returns true if the URI scheme specific part is considered to be opaque
     *
     * @return bool
     */
    public function isOpaque();

    /**
     * Returns an instance resolved according to a given URI
     *
     * This method MUST retain the state of the current instance, and return
     * an instance resolved according to supplied URI
     *
     * @param Uri $rel the relative URI
     *
     * @return static
     *
     * @see https://tools.ietf.org/html/rfc3986#section-5.2
     */
    public function resolve(Uri $rel);
}
