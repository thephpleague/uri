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
    public function toArray()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getOffsets($data = null)
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
        $orig = $this->__toString();
        if (empty($orig) || static::$delimiter !== $orig[0]) {
            $orig = static::$delimiter.$orig;
        }

        return new static($orig.static::$delimiter.$value.$appended_delimiter);
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
     * {@inheritdoc}
     */
    public function replaceWith($value, $offset)
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

        return new static(implode(static::$delimiter, $res));
    }

    /**
     * {@inheritdoc}
     */
    public function without($value)
    {
        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);

        if (is_null($this->get()) || empty($value)) {
            return clone $this;
        }

        $value_appended  = (static::$delimiter != mb_substr($value, -1, 1));
        $value_prepended = (static::$delimiter != $value[0]);

        $value = trim($value, static::$delimiter);
        $value = $this->validate($value);
        $value = implode(static::$delimiter, $value);
        $value = static::$delimiter.$value.static::$delimiter;

        $orig = $this->getUriComponent();
        $orig_appended = false;
        if (static::$delimiter != mb_substr($orig, -1, 1)) {
            $orig .= static::$delimiter;
            $orig_appended = true;
        }

        $orig_prepended = false;
        if (static::$delimiter != $orig[0]) {
            $orig = static::$delimiter.$orig;
            $orig_prepended = true;
        }

        $pos = mb_strpos($orig, $value);
        if (false === $pos) {
            return clone $this;
        }

        $length = mb_strlen($value);
        if ($value_prepended) {
            $pos    += 1;
            $length -= 1;
        }
        if ($value_appended) {
            $length -= 1;
        }

        $path = mb_substr($orig, 0, $pos).mb_substr($orig, $pos + $length);
        if ($orig_appended) {
            $path = mb_substr($path, 0, -1);
        }
        if ($orig_prepended) {
            $path = mb_substr($path, 1);
        }

        return new static($path);
    }
}
