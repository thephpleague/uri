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
namespace League\Uri\Types;

use InvalidArgumentException;
use League\Uri\Interfaces\UriPart;
use ReflectionClass;

/**
 * Common methods for Component Value Object
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait ImmutableComponentTrait
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
     * Invalid Characters list
     *
     * @var string
     */
    protected static $invalidCharactersRegex;

    /**
     * validate a string
     *
     * @param mixed $str the value to evaluate as a string
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
     * Check the string against RFC3986 rules
     *
     * @param string $str
     *
     * @throws \InvalidArgumentException If the string is invalid
     */
    protected function assertValidComponent($str)
    {
        if (!empty(static::$invalidCharactersRegex) && preg_match(static::$invalidCharactersRegex, $str)) {
            throw new InvalidArgumentException('The component contains invalid characters');
        }
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
    abstract public function getContent();

    /**
     * {@inheritdoc}
     */
    abstract public function getUriComponent();

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->getContent();
    }

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
    public function modify($value)
    {
        if ($value == $this->__toString()) {
            return $this;
        }

        return (new ReflectionClass(get_called_class()))->newInstance($value);
    }
}
