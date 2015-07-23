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
namespace League\Uri\Query;

use InvalidArgumentException;

/**
 * A Trait to parse and build a Query string
 *
 * @package League.uri
 * @since   4.0.0
 */
trait Parser
{
    /**
     * Parse que query string into an associative array
     *
     * Multiple identical key will generate an array. This function
     * differ from PHP parse_str as:
     *    - it does not modify or remove parameters keys
     *    - it does not create nested array
     *
     * @param string    $str          The query string to parse
     * @param array     $separator    The query string separator
     * @param int|false $encodingType The query string encoding mechanism
     *
     * @return array
     */
    public static function parse($str, $separator = '&', $encodingType = PHP_QUERY_RFC3986)
    {
        if ('' == $str) {
            return [];
        }
        $res     = [];
        $pairs   = explode($separator, $str);
        $decoder = static::getDecoder($encodingType);
        foreach ($pairs as $pair) {
            $res = static::parsePair($decoder, $res, $pair);
        }

        return $res;
    }

    /**
     * Parse a query string pair
     *
     * @param callable $decoder a Callable to decode the query string pair
     * @param array    $res     The associative array to add the pair to
     * @param string   $pair    The query string pair
     *
     * @return array
     */
    protected static function parsePair(callable $decoder, array $res, $pair)
    {
        $param = explode('=', $pair, 2);
        $key   = $decoder($param[0]);
        $value = null;
        if (isset($param[1])) {
            $value = $decoder($param[1]);
        }
        if (!array_key_exists($key, $res)) {
            $res[$key] = $value;

            return $res;
        }
        if (!is_array($res[$key])) {
            $res[$key] = [$res[$key]];
        }
        $res[$key][] = $value;

        return $res;
    }

    /**
     * Build a query string from an associative array
     *
     * The method expects the return value from Query::parse to build
     * a valid query string. This method differs from PHP http_build_query as:
     *
     *    - it does not modify parameters keys
     *
     * @param array     $arr          Query string parameters
     * @param array     $separator    Query string separator
     * @param int|false $encodingType Query string encoding
     *
     * @return string
     */
    public static function build(array $arr, $separator = '&', $encodingType = PHP_QUERY_RFC3986)
    {
        $encoder = static::getEncoder($encodingType);
        $arr     = array_map(function ($value) {
            return !is_array($value) ? [$value] : $value;
        }, $arr);

        $pairs = [];
        foreach ($arr as $key => $value) {
            $pairs = array_merge($pairs, static::buildPair($encoder, $encoder($key), $value));
        }

        return implode($separator, $pairs);
    }

    /**
     * Build a query key/pair association
     *
     * @param callable $encoder a Callable to encode the key/pair association
     * @param string   $key     The query string key
     * @param string   $value   The query string value
     *
     * @return string
     */
    protected static function buildPair(callable $encoder, $key, array $value = [])
    {
        return array_reduce($value, function (array $carry, $data) use ($key, $encoder) {
            $pair = $key;
            if (null !== $data) {
                $pair .= '=' . $encoder($data);
            }
            $carry[] = $pair;

            return $carry;
        }, []);
    }

    /**
     * Return the query string decoding mechanism
     *
     * @param int|false $encodingType
     *
     * @return callable
     */
    protected static function getDecoder($encodingType)
    {
        if (PHP_QUERY_RFC3986 == $encodingType) {
            return function ($value) {
                return rawurldecode($value);
            };
        }
        if (PHP_QUERY_RFC1738 == $encodingType) {
            return function ($value) {
                return urldecode($value);
            };
        }
        if (false !== $encodingType) {
            throw new InvalidArgumentException('Unknown encodingType');
        }

        return function ($value) {
            return rawurldecode(str_replace('+', ' ', $value));
        };
    }

    /**
     * Return the query string encoding mechanism
     *
     * @param int|false $encodingType
     *
     * @return callable
     */
    protected static function getEncoder($encodingType)
    {
        if (PHP_QUERY_RFC3986 == $encodingType) {
            return function ($value) {
                return rawurlencode($value);
            };
        }
        if (PHP_QUERY_RFC1738 == $encodingType) {
            return function ($value) {
                return urlencode($value);
            };
        }
        if (false !== $encodingType) {
            throw new InvalidArgumentException('Unknown encodingType');
        }

        return function ($value) {
            return $value;
        };
    }
}
