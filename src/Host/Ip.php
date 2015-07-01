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
namespace League\Uri\Host;

/**
 * A Trait to validate a IP type Host
 *
 * @package League.url
 * @since   4.0.0
 */
trait Ip
{
    /**
     * Is the Host an IPv4
     *
     * @var bool
     */
    protected $host_as_ipv4 = false;

    /**
     * Is the Host an IPv6
     *
     * @var bool
     */
    protected $host_as_ipv6 = false;

    /**
     * Tell whether the IP has a zone Identifier
     * @var boolean
     */
    protected $hasZoneIdentifier = false;

    /**
     * IPv6 Local Link binary-like prefix
     *
     * @var string
     */
    static protected $local_link_prefix = '1111111010';

    /**
     * {@inheritdoc}
     */
    public function isIp()
    {
        return $this->host_as_ipv4 || $this->host_as_ipv6;
    }

    /**
     * {@inheritdoc}
     */
    public function isIpv4()
    {
        return $this->host_as_ipv4;
    }

    /**
     * {@inheritdoc}
     */
    public function isIpv6()
    {
        return $this->host_as_ipv6;
    }

    /**
     * {@inheritdoc}
     */
    public function hasZoneIdentifier()
    {
        return $this->hasZoneIdentifier;
    }

    /**
     * Validate a Host as an IP
     *
     * @param string $str
     *
     * @throws InvalidArgumentException if the IP based host is malformed
     *
     * @return array
     */
    protected function validateIpHost($str)
    {
        $res = $this->filterIpv6Host($str);
        if (!empty($res)) {
            $this->host_as_ipv4 = false;
            $this->host_as_ipv6 = true;
            return [$res];
        }

        if (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->host_as_ipv4 = true;
            $this->host_as_ipv6 = false;
            return [$str];
        }

        $this->host_as_ipv4 = false;
        $this->host_as_ipv6 = false;
        return [];
    }

    /**
     * validate and filter a Ipv6 Hostname
     *
     * @param string $str
     *
     * @return string|false
     */
    protected function filterIpv6Host($str)
    {
        preg_match(',^([\[]?)(.*?)([\]]?)$,', $str, $matches);

        if (!in_array(strlen($matches[1].$matches[3]), [0, 2])) {
            return false;
        }

        if (false === strpos($str, '%')) {
            return filter_var($matches[2], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

        return $this->validateScopedIpv6($matches[2]);
    }

    /**
     * Scope Ip validation according to RFC6874 rules
     *
     * @see http://tools.ietf.org/html/rfc6874#section-2
     * @see http://tools.ietf.org/html/rfc6874#section-4
     *
     * @param  string $ip The ip to validate
     *
     * @return string
     */
    protected function validateScopedIpv6($ip)
    {
        $pos = strpos($ip, '%');
        if (preg_match(',[^\x20-\x7f]|[?#@\[\]],', rawurldecode(substr($ip, $pos)))) {
            return false;
        }

        $ipv6 = substr($ip, 0, $pos);
        if (!$this->isLocalLink($ipv6)) {
            return false;
        }

        $this->hasZoneIdentifier = true;

        return strtolower(rawurldecode($ip));
    }

    /**
     * Tell whether the submitted string is a local link IPv6
     *
     * @param  string  $ipv6
     *
     * @return bool
     */
    protected function isLocalLink($ipv6)
    {
        if (!filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }

        $convert = function ($carry, $char) {
            return $carry .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        };
        $res = array_reduce(str_split(unpack('A16', inet_pton($ipv6))[1]), $convert, '');

        return substr($res, 0, 10) === self::$local_link_prefix;
    }
}
