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

class Url
{
    /**
     * User and Passe Manipulation Object
     * @var Bakame\Url\Auth
     */
    private $auth;

    /**
     * Query Manipulation Object
     * @var Bakame\Url\Query
     */
    private $query;

    /**
     * Host Manipulation Object
     * @var Bakame\Url\Host
     */
    private $host;

    /**
     * Path Manipulation Object
     * @var Bakame\Url\Path
     */
    private $path;

    /**
     * Fragment Manipulation Object
     * @var Bakame\Url\Fragment
     */
    private $fragment;

    /**
     * Scheme Manipulation Object
     * @var Bakame\Url\Scheme
     */
    private $scheme;

    /**
     * Port Manipulation Object
     * @var Bakame\Url\Port
     */
    private $port;

    /**
     * The constructor
     *
     * @param Scheme   $scheme
     * @param Auth     $auth
     * @param Segment  $host
     * @param Port     $port
     * @param Segment  $path
     * @param Query    $query
     * @param Fragment $fragment
     */
    public function __construct(
        Scheme $scheme,
        Auth $auth,
        Segment $host,
        Port $port,
        Segment $path,
        Query $query,
        Fragment $fragment
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    /**
     * return the string representation for the current URL
     *
     * @return string
     */
    public function __toString()
    {
        $url = [];
        foreach (['scheme', 'auth', 'host', 'port', 'path', 'query', 'fragment'] as $component) {
            $value = $this->{$component}->__toString();
            if ('path' == $component && ! empty($value)) {
                $value = '/'.$value;
            }
            $url[] = $value;
        }

        return implode('', $url);
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
     * return the auth values
     * if null returns an array
     * otherwise it returns the specific value
     *
     * @param string|null $key
     *
     * @return string|array
     */
    public function getAuth($key = null)
    {
        return $this->auth->get($key);
    }

    /**
     * return the Host values
     * if key is null returns an array of all values
     * otherwise it returns the specific value at the given index
     *
     * @param string|null $key
     *
     * @return string|array
     */
    public function getHost($key = null)
    {
        return $this->host->get($key);
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
     * return the Path values
     * if key is null returns an array of all values
     * otherwise it returns the specific value at the given index
     *
     * @param string|null $key
     *
     * @return string|array
     */
    public function getPath($key = null)
    {
        return $this->path->get($key);
    }

    /**
     * return the Query values
     * if key is null returns an array of all values
     * otherwise it returns the specific value at the given index
     *
     * @param string|null $key
     *
     * @return string|array
     */
    public function getQuery($key = null)
    {
        return $this->query->get($key);
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
     * set Auth values
     *
     * @param mixed  $key   a string OR an array representing the data to be set
     * @param string $value is used $key is not an array is the value a to be set
     *
     * @return self
     */
    public function setAuth($key, $value = null)
    {
        $this->auth->set($key, $value);

        return $this;
    }

    /**
     * Set Host values
     * @param mixed   $value       a string OR an array representing the data to be inserted
     * @param string  $position    append or prepend where to insert the data in the host array
     * @param integer $valueBefore the data where to append the $value
     * @param integer $valueIndex  the occurenceIndex of $valueBefore if $valueBefore appears more than once
     *
     * @return self
     */
    public function setHost($value, $position = 'append', $valueBefore = null, $valueIndex = null)
    {
        if ('prepend' != $position) {
            $position = 'append';
        }
        $this->host->set($value, $position, $valueBefore, $valueIndex);

        return $this;
    }

    /**
     * set the Port value
     * if null the Port current value is unset
     *
     * @param string $value
     *
     * @return self
     */
    public function setPort($value)
    {
        $this->port->set($value);

        return $this;
    }

    /**
     * Set Path Values
     * @param mixed   $value       a string OR an array representing the data to be inserted
     * @param string  $position    append or prepend where to insert the data in the host array
     * @param integer $valueBefore the data where to append the $value
     * @param integer $valueIndex  the occurenceIndex of $valueBefore if $valueBefore appears more than once
     *
     * @return self
     */
    public function setPath($value, $position = 'append', $valueBefore = null, $valueIndex = null)
    {
        if ('prepend' != $position) {
            $position = 'append';
        }
        $this->path->set($value, $position, $valueBefore, $valueIndex);

        return $this;
    }

    /**
     * set Query values
     *
     * @param mixed  $key   a string OR an array representing the data to be set
     * @param string $value is used $key is not an array is the value a to be set
     *
     * @return self
     */
    public function setQuery($key, $value = null)
    {
        $this->query->set($key, $value);

        return $this;
    }

    /**
     * set the Fragment value
     * if null the Port current value is unset
     *
     * @param string $value
     *
     * @return self
     */
    public function setFragment($value)
    {
        $this->fragment->set($value);

        return $this;
    }

    /**
     * Unset Host Values
     * @param mixed $value a string OR an array representing the value to be removed
     *
     * @return self
     */
    public function unsetHost($value = null)
    {
        if (null === $value) {
            $this->host->clear();

            return $this;
        }
        $this->host->remove($value);

        return $this;
    }

    /**
     * Unset Path Values
     * @param mixed $value a string OR an array representing the value to be removed
     *
     * @return self
     */
    public function unsetPath($key = null)
    {
        if (null === $key) {
            $this->path->clear();

            return $this;
        }
        $this->path->remove($key);

        return $this;
    }

    /**
     * Unset Path Values
     * @param mixed $value a string OR an array representing the value to be removed
     *
     * @return self
     */
    public function unsetAuth($key = null)
    {
        if (null === $key) {
            $this->auth->clear();

            return $this;
        }
        $this->auth->remove($key);

        return $this;
    }

    /**
     * Unset Path Values
     * @param mixed $value a string OR an array representing the value to be removed
     *
     * @return self
     */
    public function unsetQuery($key = null)
    {
        if (null === $key) {
            $this->query->clear();

            return $this;
        } elseif (! is_array($key)) {
            $this->query->set($key, null);

            return $this;
        }
        $this->query->set($key);

        return $this;
    }
}
