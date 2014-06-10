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

interface UrlInterface
{
    /**
     * return the string representation for the current URL
     *
     * @return string
     */
    public function __toString();

    /**
     * return the string representation for the current URL
     * not including scheme, user, pass, host and port.
     *
     * @return string
     */
    public function getRelativeUrl();

    /**
     * return the string representation for the current URL
     * not including path, query and fragment.
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
}
