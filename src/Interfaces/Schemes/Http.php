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
namespace League\Uri\Interfaces\Schemes;

use League\Uri\Interfaces\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Value object representing a Http URI.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since   4.0.0
 */
interface Http extends Uri
{
    /**
     * Returns whether two UriInterface represents the same value
     * The comparison is based on the __toString method.
     * The following normalization is done prior to comparaison
     *
     *  - hosts are converted using the punycode algorithm
     *  - path strings is normalize by removing dot segments
     *  - query strings are sorted using their offsets
     *
     * @param UriInterface $uri
     *
     * @return bool
     */
    public function sameValueAs(UriInterface $uri);
}
