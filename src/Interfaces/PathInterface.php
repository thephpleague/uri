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
interface PathInterface extends ComponentSegmentInterface
{
    /**
     * return the string representation for a relative path
     * {@link PathInterface} $path
     *
     * @param PathInterface $reference
     *
     * @return string
     */
    public function relativeTo(PathInterface $reference);
}
