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

use Psr\Http\Message\UriInterface;

/**
 * A common interface for URL components
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Url extends UriInterface
{
    /**
     * Return an array representation of the Url
     *
     * @return array
     */
    public function toArray();

    /**
     * Tells whether two UriInterface represents the same value
     * The Comparaison is based on the __toString method.
     * No normalization is done
     *
     * @param UriInterface $url
     *
     * @return bool
     */
    public function sameValueAs(UriInterface $url);

    /**
     * Return the string representation for the current URL
     * including the scheme and the authority parts.
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Return a new object with its path normalized
     *
     * @return static
     */
    public function normalize();

    /**
     * Tells if the standard port for the given scheme is used
     *
     * @return bool
     */
    public function hasStandardPort();

    /**
     * Resolve a new URI with a relative URI
     *
     * @param UriInterface $rel the relative URI
     *
     * @return static
     */
    public function resolve(UriInterface $rel);
}
