<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Components;

use InvalidArgumentException;

/**
 *  A class to manipulate URL Segment like components
 *
 *  @package League.url
 */
abstract class AbstractSegment extends AbstractArray
{
    /**
     * segment delimiter
     *
     * @var string
     */
    protected $delimiter;

    /**
     * regex to remove data
     *
     * @var string
     */
    protected $regexStart = '@(:?^|\/)';

    /**
     * The Constructor
     * @param mixed $data The data to add
     */
    public function __construct($data = null)
    {
        $this->set($data);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        $this->data = array_values(array_filter($this->validate($data), function ($value) {
            return ! is_null($value) && '' != $value;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return str_replace(null, '', $this->get());
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data)
    {
        $data = $this->fetchRemainingSegment($this->data, $data);
        if (! is_null($data)) {
            $this->set($data);
        }
    }

    /**
     * Sanitize a string component recursively
     *
     * @param mixed $str
     *
     * @return mixed
     */
    protected function sanitizeValue($str)
    {
        if (is_array($str)) {
            foreach ($str as &$value) {
                $value = $this->sanitizeValue($value);
            }
            unset($value);

            return $str;
        }

        $str = filter_var((string) $str, FILTER_UNSAFE_RAW, array('flags' => FILTER_FLAG_STRIP_LOW));
        $str = trim($str);

        return $str;
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetSet($offset, $value)
    {
        $data = $this->data;
        if (is_null($offset)) {
            $data[] = $value;
            $this->set($data);

            return;
        }
        $offset = filter_var($offset, FILTER_VALIDATE_INT, array('min_range' => 0));
        if (false === $offset) {
            throw new InvalidArgumentException('Offset must be an integer');
        }
        $data[$offset] = $value;
        $this->set($data);
    }

    /**
     * Validate a component
     *
     * @param mixed $data the component value to be validate
     *
     * @return array
     *
     * @throws \InvalidArgumentException If The data is invalid
     */
    abstract protected function validate($data);

    /**
     * Validate data before insertion into a URL segment based component
     *
     * @param mixed  $data      the data to insert
     * @param string $delimiter a single character delimiter
     *
     * @return array
     *
     * @throws \RuntimeException if the data is not valid
     */
    protected function validateSegment($data, $delimiter)
    {
        return $this->convertToArray($data, function ($str) use ($delimiter) {
            if ('' == $str) {
                return array();
            }
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
    protected function appendSegment(array $left, array $value, $whence = null, $whence_index = null)
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
    protected function prependSegment(array $right, array $value, $whence = null, $whence_index = null)
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
     * @param array $data  the original array
     * @param mixed $value the data to be removed (can be an array or a single segment)
     *
     * @return string|null
     *
     * @throws \RuntimeException If $value is invalid
     */
    protected function fetchRemainingSegment(array $data, $value)
    {
        $segment = implode($this->delimiter, $data);
        $part = implode($this->delimiter, $this->validate($value));
        if (! preg_match($this->regexStart.preg_quote($part, '@').'@', $segment, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $pos = $matches[0][1];

        return substr($segment, 0, $pos).substr($segment, $pos + strlen($part) + 1);
    }
}
