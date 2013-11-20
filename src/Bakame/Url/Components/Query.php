<?php
/**
* Bakame.url - A lightweight Url Parser library
*
* @author Ignace Nyamagana Butera <nyamsprod@gmail.com>
* @copyright 2013 Ignace Nyamagana Butera
* @link https://github.com/nyamsprod/Bakame.url
* @license http://opensource.org/licenses/MIT
* @version 1.0.0
* @package Bakame.url
*
* MIT LICENSE
*
* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files (the
* "Software"), to deal in the Software without restriction, including
* without limitation the rights to use, copy, modify, merge, publish,
* distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to
* the following conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
* WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
namespace Bakame\Url\Components;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use ArrayAccess;

/**
 *  A Class to manipulate URL Query String component
 *
 * @package Bakame.Url
 *
 */
class Query implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * The Query string container
     * @var array
     */
    private $data = array();

    public function __construct($query = null)
    {
        if (null !== $query) {
            $res = $query;
            if (! is_array($query)) {
                if ('?' == $res[0]) {
                    $res = substr($res, 1);
                }
                parse_str($query, $res);
            }
            $this->set($res);
        }
    }

    /**
     * does the key exists in the query
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * get the query data
     * if a key is provided it will return its associated data of null
     * if not key is provided it will return the whole data
     *
     * @param string $key the key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (! $this->has($key)) {
            return null;

        }

        return $this->data[$key];
    }

    /**
     * get the query data
     * if a key is provided it will return its associated data of null
     * if not key is provided it will return the whole data
     *
     * @param string $key the key
     *
     * @return mixed
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * set Query values
     *
     * @param mixed  $key   a string OR an array representing the data to be set
     * @param string $value is used $key is not an array is the value a to be set
     *
     * @return self
     */
    public function set($key, $value = null)
    {
        if ($key instanceof Query) {
            $key = $key->all();
        } elseif (! is_array($key)) {
            $key = array($key => $value);
        }
        $this->data = array_filter(array_merge($this->data, $key), function ($value) {
            return null !== $value;
        });

        return $this;
    }

    /**
     * Remove keys
     *
     * @param mixed $keys a string OR an array representing the key to be removed from the data
     *
     * @return self
     */
    public function remove($keys)
    {
        $keys = (array) $keys;
        foreach ($keys as $key) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Clear the query data
     *
     * @return self
     */
    public function clear()
    {
        $this->data = array();

        return $this;
    }

    /**
     * format the string representation
     * @return string
     */
    public function __toString()
    {
        return  http_build_query($this->data);
    }

    /**
     * Countable Interface
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * IteratorAggregate Interface
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * ArrayAccess Interface has alias
     * @param string $offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * ArrayAccess Interface get alias
     * @param string $offset
     *
     * @return boolean
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * ArrayAccess Interface set alias
     * @param string $offset
     * @param mixed  $value
     *
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * ArrayAccess Interface set alias
     * @param string $offset
     *
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
