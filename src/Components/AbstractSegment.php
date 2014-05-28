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

use Closure;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;
use Traversable;

/**
 *  A class to manipulate URL Segment like components
 *
 *  @package League.url
 */
abstract class AbstractSegment implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * container holder
     *
     * @var array
     */
    protected $data = array();

    /**
     * segment delimiter
     *
     * @var string
     */
    protected $delimiter;

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
        $this->data = $this->validate($data);
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
    public function toArray()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($value)
    {
        $res = array_search($value, $this->data, true);
        if (false === $res) {
            return null;
        }

        return $res;
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
     * IteratorAggregate Interface method
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Countable Interface method
     *
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;

            return;
        }
        $offset = filter_var($offset, FILTER_VALIDATE_INT, array('min_range' => 0));
        if (false === $offset) {
            throw new InvalidArgumentException('Offset must be an integer');
        }
        $this->data[$offset] = (string) $value;
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }

    /**
     * Validate a component
     *
     * @param mixed $data the component value to be validate
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException If The data is invalid
     */
    abstract protected function validate($data);

    /**
     * Validate data before insertion into a URL segment based component
     *
     * @param mixed    $data     the data to insert
     * @param \Closure $callback a callable function to be called to parse
     *                           a given string into the corrseponding component
     *
     * @return array
     *
     * @throws \RuntimeException if the data is not valid
     */
    protected function validateComponent($data, Closure $callback)
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
        return $this->validateComponent($data, function ($str) use ($delimiter) {
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
        if (! preg_match('@(:?^|\/)'.preg_quote($part, '@').'@', $segment, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $pos = $matches[0][1];

        return substr($segment, 0, $pos).substr($segment, $pos + strlen($part) + 1);
    }
}
