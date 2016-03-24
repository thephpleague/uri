<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.1
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Host as HostInterface;

/**
 * Value object representing a URI host component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Host extends AbstractHierarchicalComponent implements HostInterface
{
    use HostIpTrait;

    use HostnameInfoTrait;

    use HostnameTrait;

    /**
     * HierarchicalComponent delimiter
     *
     * @var string
     */
    protected static $separator = '.';

    /**
     * Host literal representation
     *
     * @var string
     */
    protected $host;

    /**
     * New instance
     *
     * @param null|string $host
     */
    public function __construct($host = null)
    {
        if (null !== $host) {
            $host = $this->validateString($host);
            $this->data = $this->validate($host);
            $this->setLiteral();
        }
    }

    /**
     * Returns whether or not the host is an IDN
     *
     * @return bool
     */
    public function isIdn()
    {
        return $this->isIdn;
    }

    /**
     * Returns whether or not the host is an IP address
     *
     * @return bool
     */
    public function isIp()
    {
        return $this->hostAsIpv4 || $this->hostAsIpv6;
    }

    /**
     * Returns whether or not the host is an IPv4 address
     *
     * @return bool
     */
    public function isIpv4()
    {
        return $this->hostAsIpv4;
    }

    /**
     * Returns whether or not the host is an IPv6 address
     *
     * @return bool
     */
    public function isIpv6()
    {
        return $this->hostAsIpv6;
    }

    /**
     * Returns whether or not the host has a ZoneIdentifier
     *
     * @return bool
     *
     * @see http://tools.ietf.org/html/rfc6874#section-4
     */
    public function hasZoneIdentifier()
    {
        return $this->hasZoneIdentifier;
    }

    /**
     * Host literal setter
     */
    protected function setLiteral()
    {
        $this->host = !$this->isIp() ? $this->__toString() : $this->data[0];
    }

    /**
     * Returns the instance literal representation
     * without encoding
     *
     * @return string
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
    public function getLabel($offset, $default = null)
    {
        if (isset($this->data[$offset])) {
            return $this->isIdn ? $this->data[$offset] : idn_to_ascii($this->data[$offset]);
        }

        return $default;
    }

    /**
     * Returns an array representation of the host
     *
     * @return array
     */
    public function toArray()
    {
        return $this->convertToAscii($this->data, !$this->isIdn);
    }

    /**
     * Returns the instance string representation; If the
     * instance is not defined an empty string is returned
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->data)) {
            return '';
        }

        if ($this->isIp()) {
            return $this->formatIp($this->data[0]);
        }

        return $this->formatComponentString($this->toArray(), $this->isAbsolute);
    }

    /**
     * Returns a host in his punycode encoded form
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the host transcoded using to ascii the RFC 3492 rules
     *
     * @see http://tools.ietf.org/html/rfc3492
     *
     * @return static
     */
    public function toAscii()
    {
        if ($this->isIp() || !$this->isIdn) {
            return $this;
        }

        return $this->modify($this->formatComponentString(
            $this->convertToAscii($this->data, $this->isIdn),
            $this->isAbsolute
        ));
    }

    /**
     * Returns a host in his IDN form
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the host in its IDN form using RFC 3492 rules
     *
     * @see http://tools.ietf.org/html/rfc3492
     *
     * @return static
     */
    public function toUnicode()
    {
        if ($this->isIp() || $this->isIdn) {
            return $this;
        }

        return $this->modify($this->formatComponentString($this->data, $this->isAbsolute));
    }

    /**
     * @inheritdoc
     */
    protected static function formatComponentString($data, $type)
    {
        $hostname = implode(static::$separator, array_reverse(static::validateIterator($data)));
        if (self::IS_ABSOLUTE == $type) {
            return $hostname.static::$separator;
        }

        return $hostname;
    }

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

    /**
     * @inheritdoc
     */
    public function append($component)
    {
        return $this->newCollectionInstance(array_merge(
            $this->validateComponent($component)->toArray(),
            $this->toArray()
        ));
    }
}
