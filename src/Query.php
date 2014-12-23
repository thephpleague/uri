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

use ArrayAccess;
use Countable;
use IteratorAggregate;
use League\Url\Interfaces\QueryInterface;
use RuntimeException;

/**
 *  A class to manipulate URL Query component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Query extends AbstractContainer implements
    ArrayAccess,
    Countable,
    IteratorAggregate,
    QueryInterface
{
    /**
     * The Constructor
     *
     * @param mixed $data can be string, array or Traversable
     *                    object convertible into Query String
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
        $this->data = array_filter($this->validate($data), function ($value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            return null !== $value && '' !== $value;
        });
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
        $value = $this->__toString();
        if ('' != $value) {
            $value = '?'.$value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($data)
    {
        $this->set(array_merge($this->data, $this->validate($data)));
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        return $this->convertToArray($data, function ($str) {
            if ('' == $str) {
                return [];
            }
            if ('?' == $str[0]) {
                $str = substr($str, 1);
            }

            //let's preserve the key params
            $str = preg_replace_callback('/(?:^|(?<=&))[^=[]+/', function ($match) {
                return bin2hex(urldecode($match[0]));
            }, $str);
            parse_str($str, $arr);

            return array_combine(array_map('hex2bin', array_keys($arr)), $arr);
        });
    }


    /**
     * Return a Query Parameter
     *
     * @param string $key     the query parameter key
     * @param mixed  $default the query parameter default value
     *
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        $res = $this->offsetGet($key);
        if (is_null($res)) {
            return $default;
        }

        return $res;
    }

    /**
     * Query Parameter Setter
     *
     * @param string $key   the query parameter key
     * @param mixed  $value the query parameter value
     */
    public function setParameter($key, $value)
    {
        if (is_null($key)) {
            throw new RuntimeException('offset can not be null');
        }
        $this->modify([$key => $value]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        return $this->setParameter($offset, $value);
    }
}
