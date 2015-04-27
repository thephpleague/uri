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
use InvalidArgumentException;
use IteratorAggregate;
use League\Url\Interfaces\Component;
use League\Url\Interfaces\Query as QueryInterface;
use League\Url\Util;
use Traversable;

/**
 * An abstract class to ease component creation
 *
 * @package  League.url
 * @since  1.0.0
 */
class Query implements QueryInterface
{
    /**
     * The Component Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Trait to validate a stringable variable
     */
    use Util\StringValidator;

    /**
     * a new instance
     *
     * @param string $data
     */
    public function __construct($data = null)
    {
        if (! is_null($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * return a new Query instance from an Array or a traversable object
     *
     * @param  \Traversable|array $data
     *
     * @throws \InvalidArgumentException If $data is invalid
     *
     * @return static
     */
    public static function createFromArray($data)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data, true);
        }

        if (! is_array($data)) {
            throw new InvalidArgumentException(sprintf(
                'Data passed to the method must be an array or a Traversable object; received "%s"',
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }

        return new static(http_build_query($data, '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * sanitize the submitted data
     *
     * @param string $str
     *
     * @return array
     */
    protected function validate($str)
    {
        if (is_bool($str) || is_int($str)) {
            throw new InvalidArgumentException('Data passed must be a valid string; received '.gettype($str));
        }

        $str = $this->validateString($str);
        if (strpos($str, '#')) {
            throw new InvalidArgumentException('Data passed must be a valid string;');
        }

        if (empty($str)) {
            return [];
        }

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
        if (empty($this->data)) {
            return null;
        }

        return preg_replace(
            [',=&,', ',=$,'],
            ['&', ''],
            http_build_query($this->data, '', '&', PHP_QUERY_RFC3986)
        );
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
    public function getKeys($data = null)
    {
        if (is_null($data)) {
            return array_keys($this->data);
        }

        return array_keys($this->data, $data, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key, $default = null)
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
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data, true);
        }

        if (! is_array($data)) {
            $data = $this->validate($data);
        }

        return new static(http_build_query(
            array_merge($this->data, $data),
            '',
            '&',
            PHP_QUERY_RFC3986
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function withValue($value = null)
    {
        return new static($value);
    }
}
