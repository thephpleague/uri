<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Interfaces;

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
