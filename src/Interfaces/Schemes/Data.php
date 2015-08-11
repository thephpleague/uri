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
     * Retrieve the mediatype associated with the URI.
     *
     * If no mediatype is present, this method MUST return the default parameter 'text/plain;charset=US-ASCII'.
     *
     * @see http://tools.ietf.org/html/rfc2397#section-3
     *
     * @return string The URI scheme.
     */
    public function getMediatype();

    /**
     * Retrieve the data associated with the URI.
     *
     * If no data is present, this method MUST return a empty string.
     *
     * @see http://tools.ietf.org/html/rfc2397#section-2
     *
     * @return string The URI scheme.
     */
    public function getData();

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
     * Returns an instance where the data part is base64 encoded
     *
     * This method MUST retain the state of the current instance, and return
     * an instance where the data part is base64 encoded
     *
     * @return static
     */
    public function dataToBinary();

    /**
     * Returns an instance where the data part is url encoded following RFC3986 rules
     *
     * This method MUST retain the state of the current instance, and return
     * an instance where the data part is url encoded
     *
     * @return static
     */
    public function dataToAscii();
}
