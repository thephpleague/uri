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
    use ValidatorTrait;

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
     * Check the string against RFC3986 rules
     *
     * @param string $str
     *
     * @throws InvalidArgumentException If the string is invalid
     */
    protected function assertValidComponent($str)
    {
        if (!empty(static::$invalidCharactersRegex) && preg_match(static::$invalidCharactersRegex, $str)) {
            throw new InvalidArgumentException('The component contains invalid characters');
        }
    }

    /**
     * @inheritdoc
     */
    public function sameValueAs(UriPart $component)
    {
        return $component->getUriComponent() === $this->getUriComponent();
    }

    /**
     * @inheritdoc
     */
    abstract public function getUriComponent();

    /**
     * @inheritdoc
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
        $reservedChars = implode('', array_map(function ($value) {
            return preg_quote($value, '/');
        }, static::$characters_set));

        return preg_replace_callback(
            '/(?:[^'.$reservedChars.']+|%(?![A-Fa-f0-9]{2}))/',
            function (array $matches) {
                return rawurlencode($matches[0]);
            },
            $value
        );
    }

    /**
     * @inheritdoc
     */
    public function modify($value)
    {
        if ($value == $this->__toString()) {
            return $this;
        }

        return (new ReflectionClass(get_called_class()))->newInstance($value);
    }
}
