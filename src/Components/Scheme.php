<?php
/**
* League.url - A lightweight Url Parser library
*
* @author Ignace Nyamagana Butera <nyamsprod@gmail.com>
* @copyright 2014 Ignace Nyamagana Butera
* @link https://github.com/thephpleague/url
* @license http://opensource.org/licenses/MIT
* @version 3.0.0
* @package League.url
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
namespace League\Url\Components;

/**
 *  A Class to manipulate URL scheme component
 *
 * @package League.Url
 *
 */
class Scheme
{
    /**
     * data
     * @var string
     */
    private $data;

    public function __construct($str = null)
    {
        $this->set($str);
    }

    /**
     * return the data
     * @return string
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * set the data
     * @param string $value
     *
     * @return self
     */
    public function set($value = null)
    {
        if (null !== $value) {
            $value = filter_var($value, FILTER_VALIDATE_REGEXP, array(
                'options' => array(
                    'regexp' => '/^http(s?)$/i',
                    'default' => null
                )
            ));
        }
        $this->data = $value;

        return $this;
    }

    /**
     * format the data string representation
     * @return string
     */
    public function __toString()
    {
        return $this->data;
    }
}
