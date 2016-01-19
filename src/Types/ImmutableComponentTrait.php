<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.0
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
     * Path Character Decoding Regular Expression
     *
     * @var string
     */
    protected static $characters_set_compiled;

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
        if (isset(static::$invalidCharactersRegex) && preg_match(static::$invalidCharactersRegex, $str)) {
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
     * Return the precompiled Path Character Decoding Regular Expression
     *
     * @return string
     */
    protected static function getReservedRegex()
    {
        if (!isset(static::$characters_set_compiled)) {
            $reserved = preg_quote(implode('', static::$characters_set), '/');
            static::$characters_set_compiled = '/(?:[^'.$reserved.']+|%(?![A-Fa-f0-9]{2}))/S';
        }
        return static::$characters_set_compiled;
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
        $str = preg_replace_callback(
            self::getReservedRegex(),
            function (array $matches) {
                return rawurlencode($matches[0]);
            },
            $value
        );

        return preg_replace_callback(',(?<encode>%[0-9a-f]{2}),', function (array $matches) {
            return strtoupper($matches['encode']);
        }, $str);
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
