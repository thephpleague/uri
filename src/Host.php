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
use League\Url\Interfaces;
use League\Url\Util;
use Traversable;

/**
* A class to manipulate URL Host component
*
* @package League.url
* @since 1.0.0
*/
class Host extends AbstractSegmentComponent implements Interfaces\Host
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
     * Trait to handle host label validation
     */
    use Util\HostValidator;

    /**
     * new Instance
     *
     * @param string $str the host
     */
    public function __construct($str = null)
    {
        $str = $this->validateString($str);
        if ('.' == mb_substr($str, 0, 1)) {
            throw new InvalidArgumentException('Malformed Host');
        }

        if (! empty($str)) {
            $this->data = $this->validate($str);
        }
    }

    /**
     * return a new Host instance from an Array or a traversable object
     *
     * @param \Traversable|array $data
     * @param bool               $is_absolute
     *
     * @throws \InvalidArgumentException If $data is invalid
     *
     * @return static
     */
    public static function createFromArray($data, $is_absolute = false)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data, false);
        }

        if (! is_array($data)) {
            throw new InvalidArgumentException('Data passed to the method must be an array or a Traversable object');
        }

        $host = implode(static::$delimiter, $data);
        if ($is_absolute) {
            $host .= static::$delimiter;
        }

        return new static($host);
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
    public function getLabel($offset, $default = null)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return $default;
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

        $str = implode(static::$delimiter, $this->data);
        if ($this->is_absolute) {
            $str .= static::$delimiter;
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $str = (string) $this->get();
        if ($this->host_as_ipv6) {
            return "[$str]";
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->__toString();
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
        return implode(static::$delimiter, array_map([$this, 'encodeLabel'], $this->data));
    }
}
