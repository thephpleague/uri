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
 * A common interface for URL components
 *
 *  @package League.url
 *  @since  3.0.0
 */
interface ComponentInterface
{
    /**
     * Set the component data
     *
     * @param mixed $data data to be added
     *
     * @return void
     */
    public function set($data);

    /**
     * Get the component data
     *
     * @return null|string
     */
    public function get();

    /**
     * String representation of an URL component
     *
     * @return string
     */
    public function __toString();

    /**
     * component representation of an URL component
     *
     * @return string
     */
    public function getUriComponent();

    /**
     * Compare two Url component object and tells whether they
     * represent the same string value
     *
     * @param ComponentInterface $component
     *
     * @return boolean
     */
    public function sameValueAs(ComponentInterface $component);
}
