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
interface Host extends SegmentComponent
{
    /**
     * Return a host label
     *
     * @param string $offset     the parameter name
     * @param mixed  $default if no offset is found the default value to return
     *
     * @return mixed
     */
    public function getLabel($offset, $default = null);

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
     * Tells wether the current object is a full qualified domain name or not
     *
     * @return bool
     */
    public function isAbsolute();

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
}
