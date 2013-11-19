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

class Auth
{
    /**
     * Auth Data
     * @var array
     */
    private $data = array(
        'user' => null,
        'pass' => null
    );

    public function __construct($user = null, $pass = null)
    {
        $this->data['user'] = $user;
        $this->data['pass'] = $pass;
    }

    /**
     * get the data
     * if a key is provided it will return its associated data of null
     * if not key is provided it will return the whole data
     *
     * @param null|string $key
     *
     * @return mixed
     */
    public function get($key = null)
    {
        if (null === $key) {
            return $this->data;
        } elseif (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * remove a given data
     *
     * @param  $key can be 'user' OR 'pass'
     *
     * @return self
     */
    public function remove($key)
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = null;
        }

        return $this;
    }

    /**
     * Clear the data
     *
     * @return self
     */
    public function clear()
    {
        $this->data = array(
            'user' => null,
            'pass' => null
        );

        return $this;
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
            $name = [$name => $value];
        }
        foreach ($name as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * format the string representation
     * @return string
     */
    public function __toString()
    {
        $user = $this->data['user'];
        $pass = $this->data['pass'];
        if (! empty($pass)) {
            $pass = ':'.$pass;
        }

        if ($user || $pass) {
            $pass .= '@';
        }

        return $user.$pass;
    }
}
