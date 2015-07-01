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
namespace League\Uri\Interfaces;

use Countable;
use IteratorAggregate;

/**
 * Value object representing a Collection.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since   4.0.0
 */
interface Collection extends Countable, IteratorAggregate
{
    const FILTER_USE_KEY    = 2;
    const FILTER_USE_VALUE  = 3;

    /**
     * Return an array representation of the collection
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns the component offsets.
     *
     * Returns the component offsets. If a value is specified
     * only the offsets associated with the given value will be returned
     *
     * @return array
     */
    public function keys();

    /**
     * Returns whether the given offset exists in the current instance
     *
     * @param string $offset
     *
     * @return bool
     */
    public function hasKey($offset);

    /**
     * Returns an instance with only the specified value
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param callable $callable the list of offset to keep from the collection
     * @param int      $flag     flag to determine what argument are sent to callback
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
