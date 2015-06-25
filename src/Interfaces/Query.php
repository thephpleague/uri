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
 * Value object representing a URL Query component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986#section-3.4
 */
interface Query extends Collection, Component
{
    /**
     * Retrieves a single query parameter.
     *
     * Retrieves a single query parameter. If the parameter has not been set,
     * returns the default value provided.
     *
     * @param string $offset  the parameter name
     * @param mixed  $default Default value to return if the parameter does not exist.
     *
     * @return mixed
     */
    public function getValue($offset, $default = null);

    /**
     * Returns an instance merge with the specified query
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified query
     *
     * @param mixed $query the data to be merged query can be
     *                     - another Interfaces\Query object
     *                     - a Traversable object
     *                     - an array
     *                     - a string or a Stringable object
     *
     * @return static
     */
    public function merge($query);
}
