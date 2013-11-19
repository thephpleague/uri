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

class Port
{
    /**
     * Port content
     * @var string
     */
    private $data;

    public function __construct($str = null)
    {
        $this->set($str);
    }

    /**
     * get the Port value
     * @return string
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * set the Port value
     * @param string $value
     *
     * @return self
     */
    public function set($value = null)
    {
        if (null === $value) {
            $this->data = null;

            return $this;
        }
        $this->data = filter_var($value, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 1, 'default' => 80)
        ));

        return $this;
    }

    /**
     * format the string representation
     * @return string
     */
    public function __toString()
    {
        $str = '';
        if (! empty($this->data) && 80 != $this->data) {
            $str = ':'.$this->data;
        }

        return $str;
    }
}
