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
namespace Bakame\Url;

class Query
{
    /**
     * The Query string container
     * @var array
     */
    private $data = array();

    public function __construct($query = '')
    {
        parse_str($query, $res);
        $this->data = $res;
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
        return array_key_exists($key, $this->data);
    }

    /**
     * get the query data
     * if a key is provided it will return its associated data of null
     * if not key is provided it will return the whole data
     *
     * @param null|string $key the key
     *
     * @return mixed
     */
    public function get($key = null)
    {

        if (null == $key) {
            return $this->data;
        } elseif ($this->has($key)) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * set Query values
     *
     * @param mixed  $name  a string OR an array representing the data to be set
     * @param string $value is used $key is not an array is the value a to be set
     *
     * @return self
     */
    public function set($name, $value = null)
    {
        if (! is_array($name)) {
            $name = array($name => $value);
        }
        $this->data = array_filter($name + $this->data, function ($value) {
            return null !== $value;
        });

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
        $str = http_build_query($this->data);

        if (! empty($str)) {
            $str = '?'.$str;
        }

        return $str;
    }
}
