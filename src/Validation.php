<?php

namespace League\Url;

use InvalidArgumentException;
use RuntimeException;
use Traversable;

abstract class Validation
{
    /**
     * encode query string according to RFC 1738
     */
    const PHP_QUERY_RFC1738 = 1;

    /**
     * encode query string according to RFC 3986
     */
    const PHP_QUERY_RFC3986 = 2;

    /**
     * Url encode the query string
     *
     * @param array   $str           the array to encode as a query string
     * @param integer $encoding_type the encoding RFC followed
     *
     * @return string
     */
    protected static function encode(array $str, $encoding_type)
    {
        if (defined('PHP_QUERY_RFC3986')) {
            return http_build_query($str, '', '&', $encoding_type);
        }
        $query = http_build_query($str);
        if (self::PHP_QUERY_RFC3986 != $encoding_type) {
            return $query;
        }

        return str_replace(array('%E7', '+'), array('~', '%20'), $query);
    }

    /**
     * Validate the Query String Encoding Mode
     *
     * @param integer $encoding_type
     *
     * @return integer
     */
    protected static function validateEncodingType($encoding_type)
    {
        static $arr = array(self::PHP_QUERY_RFC3986 => 1, self::PHP_QUERY_RFC1738 => 1);
        if (isset($arr[$encoding_type])) {
            return $encoding_type;
        }

        return self::PHP_QUERY_RFC1738;
    }

    /**
     * Validate the URL Port component
     *
     * @param integer $str
     *
     * @return integer|null
     */
    protected static function validatePort($str)
    {
        $str = self::sanitizeComponent($str);
        if (is_null($str)) {
            return $str;
        }

        return filter_var($str, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 1, 'default' => null)
        ));
    }

    /**
     * Validate the URL Scheme component
     *
     * @param string $str
     *
     * @return string|null
     */
    protected static function validateScheme($str)
    {
        $str = self::sanitizeComponent($str);
        if (is_null($str)) {
            return $str;
        }

        $str = filter_var($str, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => '/^http(s?)$/i')
        ));

        if (! $str) {
            throw new RuntimeException('This class only deals with http URL');
        }

        return $str;
    }

    /**
     * Validate data before insertion into a URL segment based component
     *
     * @param mixed  $data     the data to insert
     * @param string $callback a callable function to be called to parse
     *                         a given string into the corrseponding component
     *
     * @return array
     *
     * @throws RuntimeException if the data is not valid
     */
    protected static function validateComponent($data, $callback)
    {
        if (is_null($data)) {
            return array();
        } elseif ($data instanceof Traversable) {
            return iterator_to_array($data);
        } elseif (is_string($data) || (is_object($data)) && (method_exists($data, '__toString'))) {
            $data = (string) $data;
            $data = trim($data);
            if ('' == $data) {
                return array();
            }
            $data = $callback($data);
        }

        if (! is_array($data)) {
            throw new RuntimeException('Your submitted data could not be converted into a proper array');
        }

        return $data;
    }

    /**
     * Validate data before insertion into a URL query component
     *
     * @param mixed $data the data to insert
     *
     * @return array
     *
     * @throws RuntimeException If the data can not be converted to array
     */
    protected static function validateQuery($data)
    {
        return self::validateComponent($data, function ($str) {
            if ('?' == $str[0]) {
                $str = substr($str, 1);
            }
            parse_str($str, $arr);

            return $arr;
        });
    }

    /**
     * Validate data before insertion into a URL segment based component
     *
     * @param mixed  $data      the data to insert
     * @param string $delimiter a single character delimiter
     *
     * @return array
     *
     * @throws RuntimeException if the data is not valid
     */
    protected static function validateSegment($data, $delimiter)
    {
        return self::validateComponent($data, function ($str) use ($delimiter) {
            if ($delimiter == $str[0]) {
                $str = substr($str, 1);
            }

            return explode($delimiter, $str);
        });
    }


    /**
     * Validate Host data before insertion into a URL host component
     *
     * @param mixed $data the data to insert
     * @param array $host an array representation of a host component
     *
     * @return array
     *
     * @throws InvalidArgumentException If the added is invalid
     */
    protected static function validateHost($data, array $host = array())
    {
        $data = self::validateSegment($data, '.');
        $imploded = implode('.', $data);
        if (127 <= (count($host) + count($data))) {
            throw new InvalidArgumentException('Host may have at maximum 127 parts');
        } elseif (225 <= (strlen(implode('.', $host)) + strlen($imploded) + 1)) {
            throw new InvalidArgumentException('Host may have a maximum of 255 characters');
        } elseif (strpos($imploded, ' ') !== false || strpos($imploded, '_') !== false) {
            throw new InvalidArgumentException('Invalid Characters used to create your host');
        }
        foreach ($data as $value) {
            if (strlen($value) > 63) {
                throw new InvalidArgumentException('each label host must have a maximum of 63 characters');
            }
        }

        return $data;
    }

    /**
     * Append some data to a given array
     *
     * @param array   $left         the original array
     * @param array   $value        the data to prepend
     * @param string  $whence       the value of the data to prepend before
     * @param integer $whence_index the occurence index for $whence
     *
     * @return array
     */
    protected static function appendSegment(array $left, array $value, $whence = null, $whence_index = null)
    {
        $right = array();
        if (null !== $whence && count($found = array_keys($left, $whence))) {
            array_reverse($found);
            $index = $found[0];
            if (array_key_exists($whence_index, $found)) {
                $index = $found[$whence_index];
            }
            $right = array_slice($left, $index+1);
            $left = array_slice($left, 0, $index+1);
        }

        return array_merge($left, $value, $right);
    }

    /**
     * Prepend some data to a given array
     *
     * @param array   $right        the original array
     * @param array   $value        the data to prepend
     * @param string  $whence       the value of the data to prepend before
     * @param integer $whence_index the occurence index for $whence
     *
     * @return array
     */
    protected static function prependSegment(array $right, array $value, $whence = null, $whence_index = null)
    {
        $left = array();
        if (null !== $whence && count($found = array_keys($right, $whence))) {
            $index = $found[0];
            if (array_key_exists($whence_index, $found)) {
                $index = $found[$whence_index];
            }
            $left = array_slice($right, 0, $index);
            $right = array_slice($right, $index);
        }

        return array_merge($left, $value, $right);
    }

    /**
     * Remove some data from a given array
     *
     * @param array  $data      the original array
     * @param mixed  $value     the data to be removed (can be an array or a single segment)
     * @param string $delimiter the segment delimiter
     *
     * @return array
     */
    protected static function removeSegment(array $data, $value, $delimiter)
    {
        $segment = implode($delimiter, $data);
        $part = implode($delimiter, self::validateSegment($value, $delimiter));
        $pos = strpos($segment, $part);
        if (false === $pos) {
            return $data;
        }

        $raw = substr($segment, 0, $pos).substr($segment, $pos + strlen($part));
        if ('.' == $delimiter) {
            $data = self::validateHost($raw);
        } elseif ('/' == $delimiter) {
            $data = self::validateSegment($raw, $delimiter);
        }

        return $data;
    }

    /**
     * Sanitize a string component
     *
     * @param string $str
     *
     * @return string|null
     */
    protected static function sanitizeComponent($str)
    {
        if (is_null($str)) {
            return $str;
        }
        $str = filter_var((string) $str, FILTER_UNSAFE_RAW, array('flags' => FILTER_FLAG_STRIP_LOW));
        $str = trim($str);

        return $str;
    }

    /**
     * Sanitize URL components
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function sanitizeComponents(array $components)
    {
        $components = array_merge(array(
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ), $components);

        if (!is_null($components['scheme'])
            && is_null($components['host'])
            && !empty($components['path'])
            && strpos($components['path'], '@') !== false
        ) {
            $tmp = explode('@', $components['path'], 2);
            $components['user'] = $components['scheme'];
            $components['pass'] = $tmp[0];
            $components['path'] = $tmp[1];
            $components['scheme'] = null;
        }

        if (is_null($components['scheme']) && is_null($components['host']) && !empty($components['path'])) {
            $tmp = $components['path'];
            if (0 === strpos($tmp, '//')) {
                $tmp = substr($tmp, 2);
            }
            $components['path'] = null;
            $res = explode('/', $tmp, 2);
            $components['host'] = $res[0];
            if (isset($res[1])) {
                $components['path'] = $res[1];
            }
            if (strpos($components['host'], '@')) {
                list($auth, $components['host']) = explode('@', $components['host']);
                $components['user'] = $auth;
                $components['pass'] = null;
                if (false !== strpos($auth, ':')) {
                    list($components['user'], $components['pass']) = explode(':', $auth);
                }
            }
        }

        return $components;
    }
}
