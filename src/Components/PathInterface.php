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
 *  @since  3.2.0
 */
interface PathInterface extends SegmentInterface
{
    /**
     * return the string representation for a relative path
     * {@link PathInterface} $path
     *
     * @param PathInterface $reference
     *
     * @return string
     */
    public function getRelativePath(PathInterface $reference);
}
