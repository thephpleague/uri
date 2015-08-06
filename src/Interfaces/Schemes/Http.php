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
 * Value object representing a Http URI.
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
interface Http extends HierarchicalUri
{
    /**
     * Returns an instance resolved according to a given URI
     *
     * This method MUST retain the state of the current instance, and return
     * an instance resolved according to supplied URI
     *
     * @param Uri $rel the relative URI
     *
     * @return Uri
     *
     * @see https://tools.ietf.org/html/rfc3986#section-5.2
     */
    public function resolve(Uri $rel);
}
