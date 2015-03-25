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
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use League\Url\Interfaces\Component;
use League\Url\Interfaces\Query as QueryInterface;
use JsonSerializable;
use Traversable;

/**
 * An abstract class to ease component creation
 *
 * @package  League.url
 * @since  1.0.0
 */
class Query implements Countable, IteratorAggregate, JsonSerializable, QueryInterface
{
    /**
     * The Component Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * a new instance
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->data = $this->validate($data);
    }

    /**
     * sanitize the submitted data
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function validate($data)
    {
        if (is_null($data)) {
            return [];
        }

        if ($data instanceof Traversable) {
            return iterator_to_array($data, true);
        }

        if (is_array($data)) {
            return $data;
        }

        return $this->validateStringQuery($data);
    }

    /**
     * sanitize the submitted data
     *
     * @param string $str
     *
     * @throws InvalidArgumentException If the submitted data is not stringable
     *
     * @return array
     */
    public function validateStringQuery($str)
    {
        if (! is_scalar($str) && (is_object($str) && ! method_exists($str, '__toString'))) {
            throw new InvalidArgumentException('the submitted data can not be converted into a valid query');
        }

        $str = trim($str);
        $str = ltrim($str, '?');
        $str = preg_replace_callback('/(?:^|(?<=&))[^=|&[]+/', function ($match) {
            return bin2hex(urldecode($match[0]));
        }, $str);
        parse_str($str, $arr);

        $arr = array_combine(array_map('hex2bin', array_keys($arr)), $arr);

        return array_filter($arr, function ($value) {
            return ! is_null($value);
        });
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
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (! $this->data) {
            return null;
        }

        return http_build_query($this->data, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $res = $this->__toString();
        if (empty($res)) {
            return $res;
        }

        return '?'.$res;
    }

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
    public function toArray()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys($value = null)
    {
        if (is_null($value)) {
            return array_keys($this->data);
        }

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
    public function mergeWith($data = null)
    {
        return new static(array_merge($this->data, $this->validate($data)));
    }
}
