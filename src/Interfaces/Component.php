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
     * Get the component raw data. Can be null
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
     * Return the component string representation with its optional delimiter
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
     * @param string $value
     *
     * @return static
     */
    public function withValue($value);
}
