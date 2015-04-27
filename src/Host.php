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
namespace League\Url;

use InvalidArgumentException;
use League\Url\Interfaces\Host as HostInterface;
use League\Url\Util;
use LogicException;

/**
* A class to manipulate URL Host component
*
* @package League.url
* @since 1.0.0
*/
class Host extends AbstractSegment implements HostInterface
{
    /**
     * Bootstring parameter values for host punycode
     */
    const BASE         = 36;
    const TMIN         = 1;
    const TMAX         = 26;
    const SKEW         = 38;
    const DAMP         = 700;
    const INITIAL_BIAS = 72;
    const INITIAL_N    = 128;
    const PREFIX       = 'xn--';
    const DELIMITER    = '-';

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
     * Segment delimiter
     *
     * @var string
     */
    protected $delimiter = '.';

    /**
     * Trait to handle punycode
     */
    use Util\Punycode;

    /**
     * Trait to validate a stringable variable
     */
    use Util\StringValidator;

    /**
     * new Instance
     *
     * @param string $str the host
     */
    public function __construct($str = null)
    {
        $str = $this->validateString($str);
        if (false !== strpos($str, '..') || '.' == mb_substr($str, -1, 1)) {
            throw new InvalidArgumentException('Malformed Host');
        }

        $str = trim($str, $this->delimiter);
        if (! empty($str)) {
            $this->data = $this->validate($str);
        }
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
     * {@inheritdoc}
     */
    protected function validate($str)
    {
        $res = $this->validateIpHost($str);
        if (! empty($res)) {
            return $res;
        }

        return $this->validateStringHost($str);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateIpHost($str)
    {
        if ('[' == $str[0] && ']' == $str[strlen($str) - 1]) {
            $str = substr($str, 1, -1);
            if (! filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                throw new InvalidArgumentException('Invalid IPV6 format');
            }
            $this->host_as_ipv4 = false;
            $this->host_as_ipv6 = true;

            return [$str];
        }

        if (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->host_as_ipv4 = false;
            $this->host_as_ipv6 = true;

            return [$str];
        }

        if (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->host_as_ipv4 = true;
            $this->host_as_ipv6 = false;

            return [$str];
        }

        if (preg_match('/^[0-9\.]+$/', $str)) {
            throw new InvalidArgumentException('Invalid Host format');
        }

        $this->host_as_ipv4 = false;
        $this->host_as_ipv6 = false;

        return [];
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
        $str    = $this->lower($str);
        $labels = explode($this->delimiter, $str);
        $nb_labels = count($labels);

        $labels = array_map(function ($value) {
            $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
            $value = trim($value);

            return $this->encodeLabel($value);
        }, $labels);

        $labels = array_filter($labels, function ($value) {
            return ! empty($value);
        });

        if ($nb_labels != count($labels)) {
            throw new InvalidArgumentException('Invalid Hostname, verify its content');
        }

        if (! $this->isValidLength($labels)) {
            throw new InvalidArgumentException('Invalid Hostname, verify its length');
        }

        if (! $this->isValidContent($labels)) {
            throw new InvalidArgumentException('Invalid Hostname, verify its content');
        }

        if (! $this->isValidLabelsCount($labels)) {
            throw new InvalidArgumentException('Invalid Hostname, verify labels count');
        }

        return array_map(function ($label) {
            if (strpos($label, static::PREFIX) !== 0) {
                return $label;
            }
            return $this->decodeLabel(substr($label, strlen(static::PREFIX)));
        }, $labels);
    }

    /**
     * Validate Host label length
     *
     * @param  array $data Host labels
     *
     * @return boolean
     */
    protected function isValidLength(array $data)
    {
        $res = array_filter($data, function ($label) {
            return strlen($label) > 63;
        });

        return empty($res);
    }

    /**
     * Validated the Host Label Pattern
     *
     * @param  array $data Host segment
     *
     * @return boolean
     */
    protected function isValidContent(array $data)
    {
        $res = preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT);

        return empty($res);
    }

    /**
     * Validated the Host Label Count
     *
     * @param  array $data Host segment
     *
     * @return boolean
     */
    protected function isValidLabelsCount(array $data = [])
    {
        $labels       = array_merge($this->data, $data);
        $count_labels = count($labels);

        return $count_labels > 0 && $count_labels < 127 && 255 > strlen(implode($this->delimiter, $labels));
    }

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
    public function get()
    {
        if (empty($this->data)) {
            return null;
        }

        if ($this->isIp()) {
            return $this->data[0];
        }

        return implode($this->delimiter, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key, $default = null)
    {
        if ($this->hasKey($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function toUnicode()
    {
        return $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        return implode($this->delimiter, array_map([$this, 'encodeLabel'], $this->data));
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $str = $this->__toString();
        if ($this->host_as_ipv6) {
            return "[$str]";
        }
        return $str;
    }

    /**
     * Check if the segment modifier are usable
     *
     * @throws \LogicException if the API can not be use
     */
    protected function assertRestriction()
    {
        if ($this->isIp()) {
            throw new LogicException('The API can not be use with an IP based host.');
        }
    }
}
