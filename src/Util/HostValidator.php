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
namespace League\Url\Util;

use InvalidArgumentException;
use League\Url\Interfaces;
use LogicException;
use Traversable;

/**
* A class to manipulate URL Host component
*
* @package League.url
* @since 1.0.0
*/
trait HostValidator
{
    /**
     * SegmentComponent delimiter
     *
     * @var string
     */
    protected static $delimiter = '.';

    /**
     * Is the Host an IPv4
     * @var bool
     */
    protected $host_as_ipv4 = false;

    /**
     * Is the Host an IPv6
     * @var bool
     */
    protected $host_as_ipv6 = false;

    /**
     * Whether the Hostname ends with a dot
     *
     * @var boolean
     */
    protected $is_absolute = false;

    /**
     * Trait to handle punycode
     */
    use Punycode;

    /**
     * Validate a Host as an IP
     *
     * @param string $str
     *
     * @throws \InvalidArgumentException if the IP based host is malformed
     *
     * @return array
     */
    protected function validateIpHost($str)
    {
        $res = $this->filterIpv6Host($str);
        if (! empty($res)) {
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
     * @return string
     *
     * @throws \InvalidArgumentException If malformed IPV6 format
     */
    protected function filterIpv6Host($str)
    {
        if (preg_match(',^\[(.*)\]$,', $str, $matches)) {
            if (! filter_var($matches[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                throw new InvalidArgumentException('Invalid IPV6 format');
            }
            return $matches[1];
        }

        return filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Validate a string only host
     *
     * @param  string $str
     *
     * @throws InvalidArgumentException If the string failed to be a valid hostname
     *
     * @return array
     */
    protected function validateStringHost($str)
    {
        if ('.' == mb_substr($str, -1, 1)) {
            $this->is_absolute = true;
            $str = mb_substr($str, 0, -1);
        }

        $labels = array_map(function ($value) {
            $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);

            return $this->encodeLabel(trim($value));
        }, explode(static::$delimiter, $this->lower($str)));

        $this->assertValidHost($labels);

        return array_map(function ($label) {
            if (strpos($label, static::PREFIX) !== 0) {
                return $label;
            }
            return $this->decodeLabel(substr($label, strlen(static::PREFIX)));
        }, $labels);
    }

    /**
     * Convert to lowercase a string without modifying unicode characters
     *
     * @param  string $str
     *
     * @return string
     */
    protected function lower($str)
    {
        $res = [];
        for ($i = 0, $length = mb_strlen($str, 'UTF-8'); $i < $length; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            if (ord($char) < 128) {
                $char = strtolower($char);
            }
            $res[] = $char;
        }

        return implode('', $res);
    }

    /**
     * Validate a String Label
     *
     * @param array $labels found host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function assertValidHost(array $labels)
    {
        $verifs = array_filter($labels, function ($value) {
            return ! empty($value) && ! preg_match('/^[0-9]+$/', $value);
        });

        if ($verifs != $labels) {
            throw new InvalidArgumentException('Invalid Hostname, verify labels');
        }

        $this->isValidLength($labels);
        $this->isValidLabelsCount($labels);
        $this->isValidContent($labels);
    }

    /**
     * Validate Host label length
     *
     * @param  array $data Host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function isValidLength(array $data)
    {
        $res = array_filter($data, function ($label) {
            return strlen($label) > 63;
        });

        if (! empty($res)) {
            throw new InvalidArgumentException('Invalid Hostname, verify its length');
        }
    }

    /**
     * Validated the Host Label Pattern
     *
     * @param  array $data Host SegmentComponent
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function isValidContent(array $data)
    {
        $res = preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT);

        if (! empty($res)) {
            throw new InvalidArgumentException('Invalid Hostname, verify its content');
        }
    }

    /**
     * Validated the Host Label Count
     *
     * @param  array $data Host SegmentComponent
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function isValidLabelsCount(array $data = [])
    {
        $labels       = array_merge($this->data, $data);
        $count_labels = count($labels);
        $res = $count_labels > 0 && $count_labels < 127 && 255 > strlen(implode(static::$delimiter, $labels));
        if (! $res) {
            throw new InvalidArgumentException('Invalid Hostname, verify labels count');
        }
    }
}
