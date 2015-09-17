<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
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
     * Tell whether the Host component is undefined or not
     *
     * @var bool
     */
    protected $isDefined = false;

    /**
     * New instance 
     *
     * @param null|string $host
     */
    public function __construct($host = null)
    {
        if (null !== $host) {
            $this->isDefined = true;
            $host = $this->validateString($host);
            $this->data = $this->validate($host);
            $this->setLiteral();
        }
    }

    /**
     * Host literal setter
     */
    protected function setLiteral()
    {
        $this->host = !$this->isIp() ? $this->__toString() : $this->data[0];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getLabel($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->isIdn ? $this->data[$key] : idn_to_ascii($this->data[$key]);
        }

        return $default;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->convertToAscii($this->data, !$this->isIdn);
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        if (!$this->isDefined) {
            return null;
        }

        if (empty($this->data)) {
            return '';
        }

        if ($this->isIp()) {
            return $this->formatIp($this->data[0]);
        }

        return $this->formatComponentString($this->toArray(), $this->isAbsolute);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
