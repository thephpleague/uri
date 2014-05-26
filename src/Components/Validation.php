<?php

namespace League\Url\Components;

use Closure;
use Traversable;
use RuntimeException;

abstract class Validation
{

    abstract public function validate($data);

    /**
     * Validate data before insertion into a URL segment based component
     *
     * @param mixed    $data     the data to insert
     * @param \Closure $callback a callable function to be called to parse
     *                           a given string into the corrseponding component
     *
     * @return array
     *
     * @throws RuntimeException if the data is not valid
     */
    protected static function validateComponent($data, Closure $callback)
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
     * @return string|null
     */
    protected static function removeSegment(array $data, $value, $delimiter)
    {
        $segment = implode($delimiter, $data);
        $part = implode($delimiter, self::validateSegment($value, $delimiter));
        $pos = strpos($segment, $part);
        if (false === $pos) {
            return null;
        }

        return substr($segment, 0, $pos).substr($segment, $pos + strlen($part));
    }

    /**
     * Sanitize a string component
     *
     * @param mixed $str
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
}
