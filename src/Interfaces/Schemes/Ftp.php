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
 * Value object representing a FTP URI.
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
interface Ftp extends HierarchicalUri
{
    /**
     * Retrieve the optional typecode associated to the path component of the URI.
     *
     * If no typecode is present, this method MUST return an empty string.
     *
     * The value returned MUST be one of the characters "a", "i", or "d", per RFC 1738
     * Section 3.2.2
     *
     * The leading ";type=" sequence is not part of the typecode and MUST NOT be
     * added.
     *
     * @see http://tools.ietf.org/html/rfc1738#section-3.2.2
     *
     * @return string The URI scheme.
     */
    public function getTypecode();

    /**
     * Return an instance with the specified typecode.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified typecode appended to the path.
     *
     * An empty typecode is equivalent to removing the typecode.
     *
     * @param string $type The typecode to use with the new instance.
     *
     * @throws InvalidArgumentException for invalid typecode.
     *
     * @return static
     *
     */
    public function withTypecode($type);
}
