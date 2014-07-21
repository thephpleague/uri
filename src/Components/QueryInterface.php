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
 * A common interface for URL Query component
 *
 *  @package League.url
 *  @since  3.0.0
 */
interface QueryInterface extends ComponentArrayInterface
{
    /**
     * modify/update a Query component
     *
     * @param mixed $data the data can be a array, a Traversable or a string
     *
     * @return void
     */
    public function modify($data);
}
