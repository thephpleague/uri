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
 * Value object representing a URL Host component.
 *
 * @package  League.url
 * @since  4.0.0
 */
interface Host extends CollectionComponent
{
    const HOST_AS_UNICODE = 1;

    const HOST_AS_ASCII   = 2;

    /**
     * Retrieves a single host label.
     *
     * Retrieves a single host label. If the label offset has not been set,
     * returns the default value provided.
     *
     * @param string $offset  the label offset
     * @param mixed  $default Default value to return if the offset does not exist.
     *
     * @return mixed
     */
    public function getLabel($offset, $default = null);

    /**
     * Returns the string representation of a host using the punycode algorythm
     *
     * @return string
     */
    public function toAscii();

    /**
     * Returns whether or not the host is a full qualified domain name
     *
     * @return bool
     */
    public function isAbsolute();

    /**
     * Returns whether or not the host is an IP address
     *
     * @return bool
     */
    public function isIp();

    /**
     * Returns whether or not the host is an IPv4 address
     *
     * @return bool
     */
    public function isIpv4();

    /**
     * Returns whether or not the host is an IPv6 address
     *
     * @return bool
     */
    public function isIpv6();
}
