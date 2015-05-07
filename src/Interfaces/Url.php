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
 * Value object representing a URL.
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
     * Returns whether two UriInterface represents the same value
     * The Comparaison is based on the __toString method.
     * No normalization is done
     *
     * @param UriInterface $url
     *
     * @return bool
     */
    public function sameValueAs(UriInterface $url);

    /**
     * Returns whether a Url is absolute or relative. An Url is
     * said to be absolute if is has a non empty scheme.
     *
     * @return bool
     */
    public function isAbsolute();

    /**
     * Returns an instance with the path normalized
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains a normalize path.
     *
     * @return static
     */
    public function normalize();

    /**
     * Returns whether the standard port for the given scheme is used, when
     * the scheme is unknown or unsupported will the method return false
     *
     * @return bool
     */
    public function hasStandardPort();

    /**
     * Returns an instance resolve according to a given URL
     *
     * This method MUST retain the state of the current instance, and return
     * an instance resolved according to supplied URL
     *
     * @param string $rel the relative URL
     *
     * @return static
     */
    public function resolve($rel);
}
