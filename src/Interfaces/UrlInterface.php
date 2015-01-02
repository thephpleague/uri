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
 * A common interface for URL as Value Object
 *
 *  @package League.url
 *  @since  3.0.0
 */
interface UrlInterface
{
    /**
     * return the string representation for the current URL
     *
     * @return string
     */
    public function __toString();

    /**
     * return an associative array representation for the current URL
     * similar to the result of parse_url but all components are always
     * present
     *
     * @return array
     */
    public function toArray();

    /**
     * return the string representation for the current URL
     * user info
     *
     * @return string
     */
    public function getUserInfo();

    /**
     * return the string representation for the current URL
     * authority part (user, pass, host, port components)
     *
     * @return string
     */
    public function getAuthority();

    /**
     * return the string representation for the current URL
     * including the scheme and the authority parts.
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Compare two Url object and tells whether they can be considered equal
     *
     * @param \League\Url\Interfaces\UrlInterface $url
     *
     * @return boolean
     */
    public function sameValueAs(UrlInterface $url);

    /**
     * get the URL scheme component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getScheme();

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getUser();

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getPass();

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\HostInterface
     */
    public function getHost();

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getPort();

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\PathInterface
     */
    public function getPath();

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\QueryInterface
     */
    public function getQuery();

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getFragment();
}
