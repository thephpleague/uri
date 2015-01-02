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

/**
 * A common interface for URL segment like component
 *
 *  @package League.url
 *  @since  3.2.0
 */
interface PathInterface extends ComponentInterface, SegmentInterface
{
    /**
     * return a new PathInterface object normalized
     * by removing dot segment
     *
     * @return PathInterface
     */
    public function normalize();

    /**
     * Return a Segment Parameter
     *
     * @param integer $offset  the segment offset
     * @param mixed   $default the segment default value
     *
     * @return string
     */
    public function getSegment($offset, $default = null);

    /**
     * Return a Segment Parameter
     *
     * @param integer $offset the segment offset
     * @param string  $value  the segment value
     *
     * @throws \OutofBoundsException if the specified $offset is not in the Host boundaries
     *
     * @return string
     */
    public function setSegment($offset, $value);
}
