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
    use Utilities\HostValidator;

    /**
     * new Instance
     *
     * @param string $str the host
     */
    protected function init($str)
    {
        $str = $this->validateString($str);
        if (!empty($str)) {
            $this->data = $this->validate($str);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function formatComponentString($str, $type)
    {
        if (self::IS_ABSOLUTE == $type) {
            return $str.static::$delimiter;
        }

        return $str;
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
        if (!empty($res)) {
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
        return $this->format(self::HOST_AS_ASCII);
    }

    /**
     * {@inheritdoc}
     */
    public function toUnicode()
    {
        return $this->format(self::HOST_AS_UNICODE);
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
        $str = $this->data[0];
        $tmp = explode('%', $this->data[0]);
        if (isset($tmp[1])) {
            $str = $tmp[0].'%25'.rawurlencode($tmp[1]);
        }

        if ($this->host_as_ipv6) {
            return "[$str]";
        }

        return $str;
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
     * @throws InvalidArgumentException If the validation fails
     */
    protected function isValidLabelsCount(array $data = [])
    {
        $labels       = array_merge($this->data, $data);
        $count_labels = count($labels);
        $res = $count_labels > 0 && $count_labels < 127 && 255 > strlen(implode(static::$delimiter, $labels));
        if (!$res) {
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
        if ('.' == mb_substr($str, -1, 1, 'UTF-8')) {
            $this->is_absolute = self::IS_ABSOLUTE;
            $str = mb_substr($str, 0, -1, 'UTF-8');
        }

        return $str;
    }
}
