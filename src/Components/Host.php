<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Components\Host as HostInterface;

/**
 * Value object representing a URI host component.
 *
 * @package League.uri
 * @since   1.0.0
 */
class Host extends AbstractHierarchicalComponent implements HostInterface
{
    use HostIpTrait;

    use HostnameInfoTrait;

    use HostnameTrait;

    /**
     * Host literal representation
     *
     * @var string
     */
    protected $host;

    /**
     * {@inheritdoc}
     */
    protected function init($str)
    {
        $str = $this->validateString($str);
        $this->data = $this->validate($str);
        $this->setLiteral();
    }

    /**
     * Host literal setter
     */
    protected function setLiteral()
    {
        $this->host = !$this->isIp() ? $this->__toString() : $this->data[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getLiteral()
    {
        return $this->host;
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
    public function getLabel($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (empty($this->data)) {
            return null;
        }

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
     * @param array $labels Hostname labels
     *
     * @return string
     */
    protected function formatHostname(array $labels)
    {
        $hostname = implode(static::$separator, $labels);
        if ($this->isAbsolute == self::IS_ABSOLUTE) {
            $hostname .= static::$separator;
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
     * @param array $labels Host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function assertLabelsCount(array $labels)
    {
        if (127 <= count(array_merge($this->data, $labels))) {
            throw new InvalidArgumentException('Invalid Hostname, verify labels count');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function formatComponentString($str, $type)
    {
        if (self::IS_ABSOLUTE == $type) {
            return $str.static::$separator;
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
