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

/**
* Value object representing a URL host component.
*
* @package League.url
* @since 1.0.0
*/
class Host extends AbstractCollectionComponent implements Interfaces\Host
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
     * Constants for host formatting
     */
    const HOST_AS_UNICODE = 1;
    const HOST_AS_ASCII   = 2;

    /**
     * Trait to handle host label validation
     */
    use Util\HostValidator;

    /**
     * new Instance
     *
     * @param string $str the host
     */
    protected function init($str)
    {
        $str = $this->validateString($str);
        if (! empty($str)) {
            $this->data = $this->validate($str);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromArray($data, $is_absolute = self::IS_RELATIVE)
    {
        if (! in_array($is_absolute, [self::IS_ABSOLUTE, self::IS_RELATIVE])) {
            throw new InvalidArgumentException('Please verify the submitted constant');
        }
        $component = implode(static::$delimiter, static::validateIterator($data));
        if ($is_absolute == self::IS_ABSOLUTE) {
            $component .= static::$delimiter;
        }

        return new static($component);
    }

    /**
     * validate the submitted data
     *
     * @param string $str
     *
     * @return array
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
    public function __toString()
    {
        return $this->format(self::HOST_AS_UNICODE);
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        return $this->format(self::HOST_AS_ASCII);
    }

    /**
     * Format the Host output
     *
     * @param  int $enc_type self::HOST_AS_ASCII or self::HOST_AS_UNICODE
     *
     * @return string
     */
    protected function format($enc_type)
    {
        if ($this->isIp()) {
            return $this->formatIp();
        }

        return $this->formatDomainName($enc_type);
    }

    /**
     * Format an IP for string representation of the Host
     *
     * @return string
     */
    protected function formatIp()
    {
        if ($this->host_as_ipv6) {
            return "[".$this->data[0]."]";
        }

        return $this->data[0];
    }

    /**
     * Format an Domain name for string representation of the Host
     *
     * @param  int $enc_type self::HOST_AS_ASCII or self::HOST_AS_UNICODE
     *
     * @return string
     */
    protected function formatDomainName($enc_type)
    {
        $data = $this->data;
        if ($enc_type == self::HOST_AS_ASCII) {
            $data = array_map([$this, 'encodeLabel'], $this->data);
        }

        $str = implode(static::$delimiter, $data);
        if ($this->is_absolute == self::IS_ABSOLUTE) {
            $str .= static::$delimiter;
        }

        return $str;
    }

    /**
     * Validated the Host Label Count
     *
     * @param array $data Host CollectionComponent
     *
     * @throws \InvalidArgumentException If the validation fails
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

    /**
     * set the FQDN property
     *
     * @param string $str
     *
     * @return string
     */
    protected function setIsAbsolute($str)
    {
        $this->is_absolute = self::IS_RELATIVE;
        if ('.' == mb_substr($str, -1, 1)) {
            $this->is_absolute = self::IS_ABSOLUTE;
            $str = mb_substr($str, 0, -1);
        }

        return $str;
    }
}
