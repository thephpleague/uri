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
 * A common interface for URL components
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Component
{
    /**
     * Get the component data
     *
     * @return null|string
     */
    public function get();

    /**
     * Return the component string representation
     *
     * @return string
     */
    public function __toString();

    /**
     * Return a user friendly string representation to ease URL construction
     *
     * @return string
     */
    public function getUriComponent();

    /**
     * Tells whether two component represent the same value
     * The Comparaison is based on the getUriComponent method
     *
     * @param Component $component
     *
     * @return bool
     */
    public function sameValueAs(Component $component);

    /**
     * Returns a new object with the given value
     *
     * @return static
     */
    public function withValue($data = null);
}
