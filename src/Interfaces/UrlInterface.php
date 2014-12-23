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
     * return the string representation an URL relative to another UrlInterface
     *
     * @param UrlInterface $ref_url
     *
     * @return string
     */
    public function getUrl(UrlInterface $ref_url = null);

    /**
     * Compare two Url object and tells whether they can be considered equal
     *
     * @param \League\Url\Interfaces\UrlInterface $url
     *
     * @return boolean
     */
    public function sameValueAs(UrlInterface $url);

    /**
     * Set the URL scheme component
     *
     * @param string $data
     *
     * @return self
     */
    public function setScheme($data);

    /**
     * get the URL scheme component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getScheme();

    /**
     * Set the URL user component
     *
     * @param string $data
     *
     * @return self
     */
    public function setUser($data);

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getUser();

    /**
     * Set the URL pass component
     *
     * @param string $data
     *
     * @return self
     */
    public function setPass($data);

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getPass();

    /**
     * Set the URL host component
     *
     * @param string|array|\Traversable $data
     *
     * @return self
     */
    public function setHost($data);

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\HostInterface
     */
    public function getHost();

    /**
     * Set the URL port component
     *
     * @param string|integer $data
     *
     * @return self
     */
    public function setPort($data);

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getPort();

    /**
     * Set the URL path component
     *
     * @param string|array|\Traversable $data
     *
     * @return self
     */
    public function setPath($data);

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\PathInterface
     */
    public function getPath();

    /**
     * Set the URL query component
     *
     * @param string|array|\Traversable $data
     *
     * @return self
     */
    public function setQuery($data);

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\QueryInterface
     */
    public function getQuery();

    /**
     * Set the URL fragment component
     *
     * @param string $data
     *
     * @return self
     */
    public function setFragment($data);

    /**
     * get the URL pass component
     *
     * @return \League\Url\Interfaces\ComponentInterface
     */
    public function getFragment();
}
