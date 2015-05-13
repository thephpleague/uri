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

use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * Value object representing a URL Query component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Query extends Component, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Return an array representation of the Query
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns query formatted according to given separator and encoded type
     *
     * @param  string $separator query separator
     * @param  int $enc_type  encoding type using PHP_QUERY_* constant
     *
     * @return string
     */
    public function format($separator, $enc_type);

    /**
     * Returns the query parameters. If a specific value is specified
     * only the parameters associated with the given value will be returned
     *
     * @param mixed $data
     *
     * @return array
     */
    public function offsets($data = null);

    /**
     * Returns whether the given parameter exists in the Query object
     *
     * @param string $offset
     *
     * @return bool
     */
    public function hasOffset($offset);

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
    public function getParameter($offset, $default = null);

    /**
     * Returns an instance merge with the specified query
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified query
     *
     * @param Query|Traversable|array $query the data to be merged
     *
     * @return static
     */
    public function merge($query);

    /**
     * Returns an instance without the specified parameters
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified query
     *
     * @param array $offsets a list of query parameters to be removed
     *
     * @return static
     */
    public function without(array $offsets);
}
