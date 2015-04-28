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
 * An interface for URL Host component
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Host extends Segment
{
    /**
     * Tell whether the host is an IP
     *
     * @return bool
     */
    public function isIp();

    /**
     * Tell whether the host is an IPv4
     *
     * @return bool
     */
    public function isIpv4();

    /**
     * Tell whether the host is an IPv6
     *
     * @return bool
     */
    public function isIpv6();

    /**
     * Return the unicode string representation of a host
     *
     * @return string
     */
    public function toUnicode();

    /**
     * Return the ascii string representation of a host
     *
     * @return string
     */
    public function toAscii();

    /**
     * Return a host label
     *
     * @param string $key     the parameter name
     * @param mixed  $default if no key is found the default value to return
     *
     * @return mixed
     */
    public function getLabel($key, $default = null);
}
