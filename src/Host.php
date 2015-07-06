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
        if ($this->isIp()) {
            return $this->formatIp($this->data[0]);
        }

        return $this->formatHostname(!$this->isIdn ? array_map('idn_to_ascii', $this->data) : $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        if ($this->isIp() || !$this->isIdn) {
            return $this;
        }

        return $this->modify($this->formatHostname(array_map('idn_to_ascii', $this->data)));
    }

    /**
     * {@inheritdoc}
     */
    public function toUnicode()
    {
        if ($this->isIp() || $this->isIdn) {
            return $this;
        }

        return $this->modify($this->formatHostname($this->data));
    }

    /**
     * string representation of a hostname
     *
     * @param  array  $labels Hostname labels
     *
     * @return string
     */
    protected function formatHostname(array $labels)
    {
        $hostname = implode(static::$delimiter, $labels);
        if ($this->isAbsolute == self::IS_ABSOLUTE) {
            $hostname .= static::$delimiter;
        }

        return $hostname;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutZoneIdentifier()
    {
        if ($this->hasZoneIdentifier) {
            return $this->modify(substr($this->data[0], 0, strpos($this->data[0], '%')));
        }

        return $this;
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
        if (127 <= count(array_merge($this->data, $data))) {
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
        $this->isAbsolute = self::IS_RELATIVE;
        if ('.' == mb_substr($str, -1, 1, 'UTF-8')) {
            $this->isAbsolute = self::IS_ABSOLUTE;
            $str = mb_substr($str, 0, -1, 'UTF-8');
        }

        return $str;
    }
}
