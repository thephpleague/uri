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
 *  A Class to manipulate URL user and pass components
 *
 * @package League.Url
 *
 */
class Auth
{
    /**
     * username
     * @var string
     */
    private $username;

    /**
     * username
     * @var string
     */
    private $password;

    public function __construct($user = null, $pass = null)
    {
        $this->username = $user;
        $this->password = $pass;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setUsername($value = null)
    {
        $this->username = $value;

        return $this;
    }

    public function setPassword($value = null)
    {
        $this->password = $value;

        return $this;
    }

    public function clear()
    {
         $this->username = null;
         $this->password = null;
    }

    /**
     * format the string representation
     * @return string
     */
    public function __toString()
    {
        $pass = $this->password;
        if (! empty($pass)) {
            $pass = ':'.$pass;
        }

        return $this->username.$pass;
    }
}
