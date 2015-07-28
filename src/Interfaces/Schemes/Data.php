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

use League\Uri\Interfaces;

/**
 * Value object representing a Data URI.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.uri
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc2397
 *
 * @property-read Interfaces\Scheme     $scheme
 * @property-read Interfaces\Parameters $parameters
 */
interface Data extends Uri
{
    /**
     * Retrieve the data mime type associated to the URI.
     *
     * If no mimetype is present, this method MUST return the default mimetype 'text/plain'.
     *
     * @see http://tools.ietf.org/html/rfc2397#section-2
     *
     * @return string The URI scheme.
     */
    public function getMimeType();

    /**
     * Retrieve the parameters associated with the Mime Type of the URI.
     *
     * If no parameters is present, this method MUST return the default parameter 'charset=US-ASCII'.
     *
     * @see http://tools.ietf.org/html/rfc2397#section-2
     *
     * @return string The URI scheme.
     */
    public function getParameters();

    /**
     * Save the data to a specific file
     *
     * @param string $path The path to the file where to save the data
     * @param string $mode The mode parameter specifies the type of access you require to the stream.
     *
     * @throws \RuntimeException if the path is not reachable
     *
     * @return \SplFileObject
     */
    public function save($path, $mode);

    /**
     * Tell whether the URI contain binary data
     *
     * @return bool
     */
    public function isBinaryData();

    /**
     * Return an instance with the specified mediatype parameters.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified mediatype parameters.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getParameters().
     *
     * An empty mediatype parameters value is equivalent to removing the mediatype parameters.
     *
     * @param string $parameters The mediatype parameters to use with the new instance.
     *
     * @throws \InvalidArgumentException for invalid query strings.
     * @return self                      A new instance with the specified mediatype parameters.
     *
     */
    public function withParameters($parameters);

    /**
     * Return an instance with update mediatype arameters
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified mediatype parameters data
     *
     * @param mixed $parameters the data to be merged can be
     *                          - another Interfaces\Parameters object
     *                          - a Traversable object
     *                          - an array
     *                          - a string or a Stringable object
     *
     * @return static
     */
    public function mergeParameters($parameters);

    /**
     * Return an instance without the specified parameters
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the specified query data
     *
     * @param callable|array $keys the list of keys to remove from the parameters
     *                             if a callable is given it should filter the list
     *                             of keys to remove from the query string
     *
     * @return static
     */
    public function withoutParameters($keys);
}
