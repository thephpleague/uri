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
namespace League\Url\Components;

/**
 * A common interface for URL segment like component
 *
 *  @package League.url
 *  @since  3.1.0
 */
interface HostInterface extends SegmentInterface
{
    /**
     * Return the unicode string representation of a hostname
     *
     * @return string
     */
    public function toUnicode();

    /**
     * Return the ascii string representation of a hostname
     *
     * @return string
     */
    public function toAscii();
}
