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

/**
 * Value object representing a URL Collection like component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Collection extends Countable, IteratorAggregate
{
    const FILTER_USE_KEY = 2;

    const FILTER_USE_VALUE = 1;

    /**
     * Return an array representation of the CollectionComponent
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns the component offsets.
     *
     * Returns the component offsets. If a specific value is specified
     * only the offsets associated with the given value will be returned
     *
     * @param mixed $data optional
     *
     * @return array
     */
    public function offsets();

    /**
     * Returns whether the given offset exists in the current instance
     *
     * @param string|int $offset
     *
     * @return bool
     */
    public function hasOffset($offset);

    /**
     * Returns an instance with only the specified value
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param callable $callable the list of offset to keep from the collection
     * @param int      $flag     Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filter(callable $callable, $flag = self::FILTER_USE_VALUE);

    /**
     * Returns an instance without the specified offsets
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param callable|array $offsets the list of offset to remove from the collection
     *
     * @return static
     */
    public function without($offsets);
}
