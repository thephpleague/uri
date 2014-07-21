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
 * A common interface for complex URL components
 *
 *  @package League.url
 *  @since  3.0.0
 */
interface ComponentArrayInterface extends ComponentInterface
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
}
