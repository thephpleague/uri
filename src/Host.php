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
namespace League\Uri;

use InvalidArgumentException;

/**
 * Value object representing a URL host component.
 *
 * @package League.url
 * @since   1.0.0
 */
class Host extends AbstractHierarchicalComponent implements Interfaces\Host
{
    /**
     * Constants for host formatting
     */
    const HOST_AS_UNICODE = 1;
    const HOST_AS_ASCII   = 2;

    /**
     * Ip host validation and properties
     */
    use Host\Ip;

    /**
     * hostname validation and properties
     */
    use Host\Hostname;

    /**
     * {@inheritdoc}
     */
    protected function init($str)
    {
        $str = $this->validateString($str);
        if (!empty($str)) {
            $this->data = $this->validate($str);
        }
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
        return $this->isIdn ? $this->toUnicode() : $this->toAscii();
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
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
            $data = array_map('idn_to_ascii', $this->data);
        }

        $str = implode(static::$delimiter, $data);
        if ($this->is_absolute == self::IS_ABSOLUTE) {
            $str .= static::$delimiter;
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutZoneIdentifier()
    {
        if (!$this->hasZoneIdentifier) {
            return $this;
        }

        return new static(substr($this->data[0], 0, strpos($this->data[0], '%')));
    }

    /**
     * Validated the Host Label Count
     *
     * @param array $data Host labels
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
