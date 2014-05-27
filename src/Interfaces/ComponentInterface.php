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

interface ComponentInterface
{
    /**
     * Set the component data
     *
     * @param mixed $data data to be added
     */
    public function set($data);

    /**
     * Get the component data
     *
     * @param string|null
     */
    public function get();

    /**
     * String representation of an URL component
     */
    public function __toString();
}
