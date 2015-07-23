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
namespace League\Uri\Interfaces;

/**
 * Value object representing a URL Port component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.uri
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986#section-3.2.3
 */
interface Port extends Component, LiteralAccess
{
    /**
     * Return an integer representation of the Port component
     *
     * @return null|int
     */
    public function toInt();
}
