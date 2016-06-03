<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.2.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Types;

/**
 * Uri Parameter validation
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
trait TranscoderTrait
{
    protected static $pathRegexp = "/(?:[^\!\$&'\(\)\*\+,;\=\:\/@%]+|%(?![A-Fa-f0-9]{2}))/S";

    protected static $queryFragmentRegexp = "/(?:[^\!\$&'\(\)\*\+,;\=\:\/@%\?]+|%(?![A-Fa-f0-9]{2}))/S";

    protected static $encodedRegexp = ',%(?<encode>[0-9a-fA-F]{2}),';

    protected static $unreservedRegexp = '/[\w\.~]+/';

    /**
     * Reserved characters list
     *
     * @var string
     */
    protected static $reservedCharactersRegex = "\!\$&'\(\)\*\+,;\=\:";

    /**
     * Encode a string according to RFC3986 Rules
     *
     * @param string $subject
     *
     * @return string
     */
    protected static function encodeQueryFragment($subject)
    {
        return self::encodeComponent($subject, self::$queryFragmentRegexp);
    }

    /**
     * Encode a component string
     *
     * @param string $subject The string to encode
     * @param string $regexp  The component specific regular expression
     *
     * @return string
     */
    protected static function encodeComponent($subject, $regexp)
    {
        $encoder = function (array $matches) {
            return rawurlencode($matches[0]);
        };

        $formatter = function (array $matches) {
            return strtoupper($matches[0]);
        };

        $subject = preg_replace_callback($regexp, $encoder, $subject);

        return preg_replace_callback(self::$encodedRegexp, $formatter, $subject);
    }

    /**
     * Encoding string according to RFC3986
     *
     * @param string $subject
     *
     * @return string
     */
    protected static function encode($subject)
    {
        return self::encodeComponent(
            $subject,
            '/(?:[^'.static::$reservedCharactersRegex.']+|%(?![A-Fa-f0-9]{2}))/S'
        );
    }

    /**
     * Decode a string according to RFC3986 Rules
     *
     * @param string $subject
     *
     * @return string
     */
    protected static function decodeQueryFragment($subject)
    {
        $decoder = function (array $matches) {

            $decode = chr(hexdec($matches['encode']));
            if (preg_match(self::$unreservedRegexp, $decode)) {
                return $matches[0];
            }

            if (preg_match('/[\[\]\+\?:]+/', $decode)) {
                return $decode;
            }

            return rawurldecode($matches[0]);
        };

        return preg_replace_callback(self::$encodedRegexp, $decoder, self::encodeQueryFragment($subject));
    }

    /**
     * Encode a path string according to RFC3986
     *
     * @param string $subject can be a string or an array
     *
     * @return string The same type as the input parameter
     */
    protected static function encodePath($subject)
    {
        return self::encodeComponent($subject, self::$pathRegexp);
    }

    /**
     * Decode a path string according to RFC3986
     *
     * @param string $subject can be a string or an array
     *
     * @return string The same type as the input parameter
     */
    protected static function decodePath($subject)
    {
        $decoder = function (array $matches) {
            return rawurldecode($matches[0]);
        };

        return preg_replace_callback(self::$pathRegexp, $decoder, $subject);
    }
}
