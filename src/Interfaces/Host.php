<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri\Interfaces;

/**
 * Value object representing a URL Host component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986#section-3.2.2
 */
interface Host extends HierarchicalComponent
{
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

    /**
     * Returns whether or not the host has a ZoneIdentifier
     *
     * @return bool
     *
     * @see http://tools.ietf.org/html/rfc6874#section-4
     */
    public function hasZoneIdentifier();

    /**
     * Returns whether or not the host is a full qualified domain name
     *
     * @return bool
     */
    public function isAbsolute();

    /**
     * Returns whether or not the host is an IDN
     *
     * @return bool
     */
    public function isIdn();

    /**
     * Tell whether the current public suffix is valid
     *
     * @return bool
     */
    public function isPublicSuffixValid();

    /**
     * Return the host public suffix
     *
     * @return string
     */
    public function getPublicSuffix();

    /**
     * Return the host registrable domain
     *
     * @return string
     */
    public function getRegisterableDomain();

    /**
     * Retrun the hostname subdomain
     *
     * @return string
     */
    public function getSubdomain();

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
     * Returns the unicode string representation of a host
     *
     * @return string
     */
    public function toUnicode();

    /**
     * Returns the ascii string representation of a host
     *
     * @return string
     */
    public function toAscii();

    /**
     * Return an host without its zone identifier according to RFC6874
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the host zone identifier according to RFC6874
     *
     * @see http://tools.ietf.org/html/rfc6874#section-4
     *
     * @return static
     */
    public function withoutZoneIdentifier();
}
