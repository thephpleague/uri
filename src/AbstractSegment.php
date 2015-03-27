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

use InvalidArgumentException;
use League\Url\Interfaces\Component;

/**
 * An abstract class to ease Segment object creation
 *
 * @package  League.url
 * @since  3.0.0
 */
abstract class AbstractSegment
{
    /**
     * The Component delimiter
     *
     * @var string
     */
    protected $delimiter;

    /**
     * The Component Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    abstract public function getUriComponent();

    /**
     * Validate incoming data
     *
     * @param string $data
     *
     * @throws InvalidArgumentException If the given data is not valid
     *
     * @return array
     */
    abstract protected function validate($data);

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Component $component)
    {
        return $component->__toString() == $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys($value = null)
    {
        if (is_null($value)) {
            return array_keys($this->data);
        }

        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);
        $value = trim($value, $this->delimiter);
        $value = $this->validate($value);
        $value = implode($this->delimiter, $value);

        return array_keys($this->data, $value, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($key, $default = null)
    {
        if ($this->hasKey($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function appendWith($value)
    {
        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);
        $appended_delimiter = '';
        if ($this->delimiter == $value[mb_strlen($value) - 1]) {
            $appended_delimiter = $this->delimiter;
        }
        $value = trim($value, $this->delimiter);
        $value = $this->validate($value);

        $value = implode($this->delimiter, $value);
        $orig = $this->__toString();
        if ($this->delimiter !== $orig[0]) {
            $orig = $this->delimiter.$orig;
        }

        return new static($orig.$this->delimiter.$value.$appended_delimiter);
    }

    /**
     * {@inheritdoc}
     */
    public function prependWith($value)
    {
        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);
        $value = trim($value, $this->delimiter);
        $value = $this->validate($value);

        $value = implode($this->delimiter, $value);
        $orig = $this->__toString();
        if ($this->delimiter !== $orig[0]) {
            $orig = $this->delimiter.$orig;
        }

        return new static($value.$orig);
    }

    /**
     * {@inheritdoc}
     */
    public function replaceWith($value, $key)
    {
        if (! empty($this->data) && ! $this->hasKey($key)) {
            return clone $this;
        }

        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);
        $value = trim($value, $this->delimiter);
        $value = $this->validate($value);

        $res = $this->data;
        $res[$key] = implode($this->delimiter, $value);

        return new static(implode($this->delimiter, $res));
    }

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
    public function without($value)
    {
        $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);

        if (is_null($this->get()) || empty($value)) {
            return clone $this;
        }

        $value_appended  = ($this->delimiter != $value[mb_strlen($value) - 1]);
        $value_prepended = ($this->delimiter != $value[0]);

        $value = trim($value, $this->delimiter);
        $value = $this->validate($value);
        $value = implode($this->delimiter, $value);
        $value = $this->delimiter.$value.$this->delimiter;

        $orig = $this->getUriComponent();
        $orig_appended = false;
        if ($this->delimiter != $orig[mb_strlen($orig) - 1]) {
            $orig .= $this->delimiter;
            $orig_appended = true;
        }

        $orig_prepended = false;
        if ($this->delimiter != $orig[0]) {
            $orig = $this->delimiter.$orig;
            $orig_prepended = true;
        }

        $pos = mb_strpos($orig, $value);
        if (false === $pos) {
            return clone $this;
        }

        $length = mb_strlen($value);
        if ($value_prepended) {
            $pos += 1;
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
