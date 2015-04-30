<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use ArrayIterator;

/**
 * An abstract class to ease Segment object creation
 *
 * @package  League.url
 * @since  3.0.0
 */
abstract class AbstractSegment extends AbstractComponent
{
    /**
     * The Component Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Segment delimiter
     *
     * @var string
     */
    protected static $delimiter;

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
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
    public function offsets($data = null)
    {
        if (is_null($data)) {
            return array_keys($this->data);
        }

        $data = filter_var($data, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $data = trim($data);
        $data = trim($data, static::$delimiter);
        $data = $this->validate($data);
        $data = implode(static::$delimiter, $data);

        return array_keys($this->data, $data, true);
    }

    /**
     * {@inheritdoc}
     */
    public function hasOffset($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Check if the segment modifier are usable
     */
    protected function assertRestriction()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function appendWith($value)
    {
        $this->assertRestriction();

        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);
        $appended_delimiter = '';
        if (static::$delimiter == $value[mb_strlen($value) - 1]) {
            $appended_delimiter = static::$delimiter;
        }
        $value = trim($value, static::$delimiter);
        $value = $this->validate($value);
        $value = implode(static::$delimiter, $value);

        return new static($this->__toString().static::$delimiter.$value.$appended_delimiter);
    }

    /**
     * {@inheritdoc}
     */
    public function prependWith($value)
    {
        $this->assertRestriction();

        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);

        $prepend_delimiter = '';
        if (static::$delimiter == $value[0]) {
            $prepend_delimiter = static::$delimiter;
        }
        $value = trim($value, static::$delimiter);
        $value = $this->validate($value);

        $value = implode(static::$delimiter, $value);
        $orig = $this->__toString();
        if (static::$delimiter !== $orig[0]) {
            $orig = static::$delimiter.$orig;
        }

        return new static($prepend_delimiter.$value.$orig);
    }

    /**
     * Return a string with the replace data
     *
     * @param  string $value  The data to replace in the current object
     * @param  int $offset The offset where to inject the new string
     *
     * @return string
     */
    protected function prepareReplaceWith($value, $offset)
    {
        if (! empty($this->data) && ! $this->hasOffset($offset)) {
            return clone $this;
        }

        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);
        $value = trim($value, static::$delimiter);
        $value = $this->validate($value);

        $res = $this->data;
        $res[$offset] = implode(static::$delimiter, $value);

        return implode(static::$delimiter, $res);
    }

    /**
     * Remove selected segments from the collection
     *
     * @param array  $offsets contains segment offsets
     *
     * @return array
     */
    protected function removeSegmentByOffsets(array $offsets)
    {
        $offsets = array_unique($offsets);
        $data    = $this->data;
        foreach ($offsets as $offset) {
            if (! array_key_exists($offset, $data)) {
                return $this->data;
            }
            unset($data[$offset]);
        }

        return $data;
    }
}
