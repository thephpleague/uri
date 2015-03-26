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

/**
 * An interface for URL Query component
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Segment extends Component, Countable
{
    /**
     * Return the query keys. If a value is specified
     * only the key for that value are returned
     *
     * @param  null|string $value
     *
     * @return array
     */
    public function getKeys($value = null);

    /**
     * Return a value from the Query object
     *
     * @param string $name    the parameter name
     * @param mixed  $default if no key is found the default value to return
     *
     * @return mixed
     */
    public function getValue($name, $default = null);

    /**
     * Tell whether the given key exists in the Query object
     *
     * @param  string $name
     *
     * @return bool
     */
    public function hasKey($name);

    /**
     * Append new data at the end of the component
     *
     * @param  string $value
     *
     * @return Segment
     */
    public function appendWith($value);

    /**
     * Prepend new data at the beginning of the component
     *
     * @param  string $value
     *
     * @return Segment
     */
    public function prependWith($value);

    /**
     * Replace data from the segment and return a new segment
     *
     * @param  string $value
     * @param  int $key
     *
     * @return Segment
     */
    public function replaceWith($value, $key);

    /**
     * Remove data from the segment and return a new segment
     *
     * @param  string $value
     *
     * @return Segment
     */
    public function without($value);
}
