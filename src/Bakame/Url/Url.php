<?php
/**
* Bakame.url - A lightweight Url Parser library
*
* @author Ignace Nyamagana Butera <nyamsprod@gmail.com>
* @copyright 2013 Ignace Nyamagana Butera
* @link https://github.com/nyamsprod/Bakame.url
* @license http://opensource.org/licenses/MIT
* @version 2.0.0
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

use Bakame\Url\Components\Scheme;
use Bakame\Url\Components\Auth;
use Bakame\Url\Components\Host;
use Bakame\Url\Components\Port;
use Bakame\Url\Components\Path;
use Bakame\Url\Components\Query;
use Bakame\Url\Components\Fragment;

/**
 *  A Class to manipulate URLs
 *
 * @package Bakame.Url
 *
 */
class Url
{
    /**
     * Scheme Manipulation Object
     * @var Bakame\Url\Components\Scheme
     */
    private $scheme;

    /**
     * User and Passe Manipulation Object
     * @var Bakame\Url\Components\Auth
     */
    private $auth;

    /**
     * Host Manipulation Object
     * @var Bakame\Url\Components\Host
     */
    private $host;

    /**
     * Port Manipulation Object
     * @var Bakame\Url\Components\Port
     */
    private $port;

    /**
     * Path Manipulation Object
     * @var Bakame\Url\Components\Path
     */
    private $path;

    /**
     * Query Manipulation Object
     * @var Bakame\Url\Components\Query
     */
    private $query;

    /**
     * Fragment Manipulation Object
     * @var Bakame\Url\Components\Fragment
     */
    private $fragment;

    /**
     * The constructor
     *
     * @param Scheme   $scheme
     * @param Auth     $auth
     * @param Host     $host
     * @param Port     $port
     * @param Path     $path
     * @param Query    $query
     * @param Fragment $fragment
     */
    public function __construct(
        Scheme $scheme,
        Auth $auth,
        Host $host,
        Port $port,
        Path $path,
        Query $query,
        Fragment $fragment
    ) {
        $this->scheme = $scheme;
        $this->auth = $auth;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    /**
     * To Enable cloning
     */
    public function __clone()
    {
        $this->scheme = clone $this->scheme;
        $this->auth = clone $this->auth;
        $this->host = clone $this->host;
        $this->port = clone $this->port;
        $this->path = clone $this->path;
        $this->query = clone $this->query;
        $this->fragment = clone $this->fragment;
    }

    /**
     * return the string representation for the current URL
     *
     * @return string
     */
    public function __toString()
    {
        $scheme = $this->scheme->__toString();
        if (! empty($scheme)) {
            $scheme .=':';
        }
        $scheme .= '//';

        $auth = $this->auth->__toString();
        if (! empty($auth)) {
            $auth .='@';
        }
        $host = $this->host->__toString();
        $port = $this->port->__toString();
        if (! empty($port)) {
            $port = ':'.$port;
        }
        $path = $this->path->__toString();
        if (! empty($path)) {
            $path = '/'.$path;
        }
        $query = $this->query->__toString();
        if (! empty($query)) {
            $query = '?'.$query;
        }
        $fragment = $this->fragment->__toString();
        if (! empty($fragment)) {
            $fragment = '#'.$fragment;
        }

        return $scheme.$auth.$host.$port.$path.$query.$fragment;
    }

    /**
     * return the scheme value
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme->get();
    }

    /**
     * set the Scheme value
     * if null the scheme current value is unset
     *
     * @param string $value
     *
     * @return self
     */
    public function setScheme($value = null)
    {
        $this->scheme->set($value);

        return $this;
    }

    /**
     * return the user value
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->auth->getUsername();
    }

    /**
     * set user name
     *
     * @param string|null $value
     *
     * @return self
     */
    public function setUsername($value = null)
    {
        $this->auth->setUsername($value);

        return $this;
    }

    /**
     * return the password value
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->auth->getPassword();
    }

    /**
     * set user password
     *
     * @param string|null $value
     *
     * @return self
     */
    public function setPassword($value = null)
    {
        $this->auth->setPassword($value);

        return $this;
    }

    /**
     * return the Port value
     *
     * @return string|null
     */
    public function getPort()
    {
        return $this->port->get();
    }

    /**
     * set the Port value
     * if null the Port current value is unset
     *
     * @param string $value
     *
     * @return self
     */
    public function setPort($value = null)
    {
        $this->port->set($value);

        return $this;
    }

    /**
     * return the Fragment value
     *
     * @return string|array
     */
    public function getFragment()
    {
        return $this->fragment->get();
    }

    /**
     * set the Fragment value
     * if null the Port current value is unset
     *
     * @param string $value
     *
     * @return self
     */
    public function setFragment($value = null)
    {
        $this->fragment->set($value);

        return $this;
    }

    /**
     * return the host component object
     *
     * @return \Bakame\Url\Components\Host
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * return the path component object
     *
     * @return \Bakame\Url\Components\Path
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * return the query component object
     *
     * @return \Bakame\Url\Components\Query
     */
    public function query()
    {
        return $this->query;
    }
}
