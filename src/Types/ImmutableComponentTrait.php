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
     * Reserved characters list
     *
     * @var string
     */
    protected static $reservedCharactersRegex = "\!\$&'\(\)\*\+,;\=\:";

    /**
     * Invalid characters list
     *
     * @var string
     */
    protected static $invalidCharactersRegex;

    /**
     * Asserts the string against RFC3986 rules
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
     * Returns whether two UriPart objects represent the same value
     * The comparison is based on the getUriComponent method
     *
     * @param UriPart $component
     *
     * @return bool
     */
    public function sameValueAs(UriPart $component)
    {
        return $component->getUriComponent() === $this->getUriComponent();
    }

    /**
     * Returns the instance string representation
     * with its optional URI delimiters
     *
     * @return string
     */
    abstract public function getUriComponent();

    /**
     * Returns the instance string representation; If the
     * instance is not defined an empty string is returned
     *
     * @return string
     */
    abstract public function __toString();

    /**
     * Encoding string according to RFC3986
     *
     * @param string $str
     *
     * @return string
     */
    protected static function encode($str)
    {
        $encoder = function (array $matches) {
            return rawurlencode($matches[0]);
        };

        $formatter = function (array $matches) {
            return strtoupper($matches['encode']);
        };

        $str = preg_replace_callback(
            '/(?:[^'.static::$reservedCharactersRegex.']+|%(?![A-Fa-f0-9]{2}))/S',
            $encoder,
            $str
        );

        return preg_replace_callback(',(?<encode>%[0-9a-f]{2}),', $formatter, $str);
    }

    /**
     * Returns an instance with the specified string
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified data
     *
     * @param string $value
     *
     * @return static
     */
    public function modify($value)
    {
        if ($value == $this->__toString()) {
            return $this;
        }

        return (new ReflectionClass(get_called_class()))->newInstance($value);
    }
}
