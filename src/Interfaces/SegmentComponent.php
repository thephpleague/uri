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
 * Value object representing a URL Segment like component.
 *
 * @package  League.url
 * @since  4.0.0
 */
interface SegmentComponent extends Component, Countable, IteratorAggregate
{
    /**
     * Return an array representation of the SegmentComponent
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
     * @param mixed $data
     *
     * @return array
     */
    public function offsets($data = null);

    /**
     * Returns whether the given offset exists in the instance
     *
     * @param string $offset
     *
     * @return bool
     */
    public function hasOffset($offset);

    /**
     * Returns an instance with the specified component appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the appended data
     *
     * @param SegmentComponent $component the component to append
     *
     * @return static
     */
    public function append(SegmentComponent $component);

    /**
     * Returns an instance with the specified component prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the prepended data
     *
     * @param SegmentComponent $component the component to prepend
     *
     * @return static
     */
    public function prepend(SegmentComponent $component);

    /**
     * Returns an instance with the modified segment
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the replaced data
     *
     * @param SegmentComponent $component the component added
     * @param int              $offset    the label offset to remove and replace by
     *                                    the given component
     *
     * @return static
     */
    public function replace(SegmentComponent $component, $offset);

    /**
     * Returns an instance without the specified offsets
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param array $offsets a list of segment offset to be removed
     *
     * @return static
     */
    public function without(array $offsets);
}
