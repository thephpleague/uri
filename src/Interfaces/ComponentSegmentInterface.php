<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.2.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Interfaces;

/**
 * A common interface for URL segment like component
 *
 *  @package League.url
 *  @since  3.0.0
 */
interface ComponentSegmentInterface extends ComponentInterface
{
    /**
     * Return the component as an array
     *
     * @return array
     */
    public function toArray();

    /**
     * Return all the keys or a subset of the keys of an array
     *
     * @return array
     */
    public function keys();

    /**
     * Append data to the component
     *
     * @param mixed   $data         the data can be a array, a Traversable or a string
     * @param string  $whence       where the data should be prepended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return void
     */
    public function append($data, $whence = null, $whence_index = null);

    /**
     * Prepend data to the component
     *
     * @param mixed   $data         the data can be a array, a Traversable or a string
     * @param string  $whence       where the data should be prepended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return void
     */
    public function prepend($data, $whence = null, $whence_index = null);

    /**
     * Remove part of the component
     *
     * @param mixed $data the data can be a array, a Traversable or a string
     *
     * @return bool
     */
    public function remove($data);

    /**
     * Return a Segment Parameter
     *
     * @param integer $key     the segment index
     * @param mixed   $default the segment default value
     *
     * @return string
     */
    public function getSegment($key, $default = null);
}
