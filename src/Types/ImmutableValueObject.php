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
namespace League\Uri\Types;

use InvalidArgumentException;
use League\Uri\Interfaces\UriPart;
use ReflectionClass;

/**
 * Common methods for Component Value Object
 *
 * @package League.uri
 * @since   4.0.0
 */
trait ImmutableValueObject
{
    /**
     * Characters to conform to RFC3986 - http://tools.ietf.org/html/rfc3986#section-2
     *
     * @var array
     */
    protected static $characters_set = [
        '!', '$', '&', "'", '(', ')', '*', '+', ',', ';', '=', ':',
    ];

    /**
     * Encoded characters to conform to RFC3986 - http://tools.ietf.org/html/rfc3986#section-2
     *
     * @var array
     */
    protected static $characters_set_encoded = [
        '%21', '%24', '%26', '%27', '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D', '%3A',
    ];

    /**
     * validate a string
     *
     * @param mixed $str
     *
     * @throws \InvalidArgumentException if the submitted data can not be converted to string
     *
     * @return string
     */
    protected function validateString($str)
    {
        if (is_object($str) && method_exists($str, '__toString') || is_string($str)) {
            return trim($str);
        }

        throw new InvalidArgumentException('The data received is not OR can not be converted into a string');
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UriPart $component)
    {
        return $component->getUriComponent() === $this->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getUriComponent();

    /**
     * {@inheritdoc}
     */
    abstract public function __toString();

    /**
     * Encoding string according to RFC3986
     *
     * @param string $value
     *
     * @return string
     */
    protected static function encode($value)
    {
        return str_replace(static::$characters_set_encoded, static::$characters_set, rawurlencode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function modify($value)
    {
        if ($value == $this->__toString()) {
            return $this;
        }

        return (new ReflectionClass(get_called_class()))->newInstance($value);
    }
}
