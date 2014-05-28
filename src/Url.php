<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use League\Url\Components\Scheme;
use League\Url\Components\Component;
use League\Url\Components\Host;
use League\Url\Components\Port;
use League\Url\Components\Path;
use League\Url\Components\Query;

/**
 * A Immutable Value Object class to manipulate URLs
 *
 * @package League.url
 */
final class Url
{
    /**
    * User
    *
    * @var League\Url\Components\Component Object
    */
    private $user;

    /**
    * Pass
    *
    * @var League\Url\Components\Component Object
    */
    private $pass;

    /**
    * Scheme
    *
    * @var League\Url\Components\Scheme Object
    */
    private $scheme;

    /**
     * Port
     *
     *@var League\Url\Components\Port Object
     */
    private $port;

    /**
     * Fragment
     *
     * @var League\Url\Components\Component Object
     */
    private $fragment;

    /**
     * Host
     *
     * @var League\Url\Components\Host Object
     */
    private $host;

    /**
     * Path
     *
     * @var League\Url\Components\Path Object
     */
    private $path;

    /**
     * Query
     *
     * @var League\Url\Components\Query Object
     */
    private $query;

    /**
     * The Constructor
     *
     * @param League\Url\Components\Scheme    $scheme   Url Scheme object
     * @param League\Url\Components\Component $user     Url Component object
     * @param League\Url\Components\Component $pass     Url Component object
     * @param League\Url\Components\Host      $host     Url Host object
     * @param League\Url\Components\Port      $port     Url Port object
     * @param League\Url\Components\Path      $path     Url Path object
     * @param League\Url\Components\Query     $query    Url Query object
     * @param League\Url\Components\Component $fragment Url Component object
     */
    public function __construct(
        Scheme $scheme,
        Component $user,
        Component $pass,
        Host $host,
        Port $port,
        Path $path,
        Query $query,
        Component $fragment
    ) {
        $this->scheme = $scheme;
        $this->user = $user;
        $this->pass = $pass;
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
        $this->user = clone $this->user;
        $this->pass = clone $this->pass;
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
        $user = $this->user->__toString();
        $pass = $this->pass->__toString();
        $host = $this->host->__toString();
        $port = $this->port->__toString();
        $path = $this->path->__toString();
        $query = $this->query->__toString();
        $fragment = $this->fragment->__toString();

        if ('' != $scheme) {
            $scheme .= ':';
        }

        if ('' != $pass) {
            $pass = ':'.$pass;
        }
        $user .= $pass;
        if ('' != $user) {
            $user .='@';
        }

        if ('' != $port) {
            $port = ':'.$port;
        }

        if ('' != $query) {
            $query = '?'.$query;
        }
        if ('' != $fragment) {
            $fragment = '#'.$fragment;
        }

        if ('' != $host || '' != $scheme) {
            $scheme .= '//';
        }

        $domain = $scheme.$user.$host.$port;
        if ('' != $path || '' != $domain) {
            $path = '/'.$path;
        }

        return $domain.$path.$query.$fragment;
    }

    /**
     * Set the URL user component
     *
     * @param string $str
     *
     * @return self
     */
    public function setUser($str)
    {
        $clone = clone $this;
        $clone->user->set($str);

        return $clone;
    }

    /**
     * get the URL user component
     *
     * @return \League\Url\Components\Component object
     */
    public function getUser()
    {
        return clone $this->user;
    }

    /**
     * Set the URL pass component
     *
     * @param string $str
     *
     * @return self
     */
    public function setPass($str)
    {
        $clone = clone $this;
        $clone->pass->set($str);

        return $clone;
    }

    /**
     * Return the current URL pass component
     *
     * @return \League\Url\Components\Component object
     */
    public function getPass()
    {
        return clone $this->pass;
    }

    /**
     * Set the URL port component
     *
     * @param string $value
     *
     * @return self
     */
    public function setPort($value)
    {
        $clone = clone $this;
        $clone->port->set($value);

        return $clone;
    }

    /**
     * Return the URL Port component
     *
     * @return \League\Url\Components\Port object
     */
    public function getPort()
    {
        return clone $this->port;
    }

    /**
     * Set the URL scheme component
     *
     * @param string $value
     *
     * @return self
     */
    public function setScheme($value)
    {
        $clone = clone $this;
        $clone->scheme->set($value);

        return $clone;
    }

    /**
     * return the URL scheme component
     *
     * @return \League\Url\Components\Scheme object
     */
    public function getScheme()
    {
        return clone $this->scheme;
    }

    /**
     * Set the URL Fragment component
     *
     * @param string $str
     *
     * @return self
     */
    public function setFragment($str)
    {
        $clone = clone $this;
        $clone->fragment->set($str);

        return $clone;
    }

    /**
     * return the URL fragment component
     *
     * @return \League\Url\Components\Component object
     */
    public function getFragment()
    {
        return clone $this->fragment;
    }

    /**
     * Set the Query String encoding type (see {@link http_build_query})
     *
     * @param integer $encoding_type
     *
     * @return self
     */
    public function setEncodingType($encoding_type)
    {
        $clone = clone $this;
        $clone->query->setEncodingType($encoding_type);

        return $clone;
    }

    /**
     * return the current Encoding type value
     *
     * @return integer
     */
    public function getEncodingType()
    {
        $clone = clone $this;

        return $clone->query->getEncodingType();
    }

    /**
     * Set the URL query component
     *
     * @param mixed $data the data to be added to the query component
     *
     * @return self
     */
    public function setQuery($data)
    {
        $clone = clone $this;
        $clone->query->set($data);

        return $clone;
    }

    /**
     * Return the current URL query component
     *
     * @return \League\Url\Components\Query object
     */
    public function getQuery()
    {
        return clone $this->query;
    }

    /**
     * Replace the current URL query component
     *
     * @param mixed $data the data to be replaced
     *
     * @return self
     */
    public function modifyQuery($data)
    {
        $clone = clone $this;
        $clone->query->modify($data);

        return $clone;
    }

    /**
     * Set the URL host component
     *
     * @param mixed $data the host data can be a array or a string
     *
     * @return self
     */
    public function setHost($data)
    {
        $clone = clone $this;
        $clone->host->set($data);

        return $clone;
    }

    /**
     * Return the current Host component
     *
     * @return \League\Url\Components\Host object
     */
    public function getHost()
    {
        return clone $this->host;
    }

    /**
     * Prepend the URL host component
     *
     * @param mixed   $data         the host data can be a array or a string
     * @param string  $whence       where the data should be prepended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function prependHost($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->host->prepend($data, $whence, $whence_index);

        return $clone;
    }

    /**
     * Append the URL host component
     *
     * @param mixed   $data         the host data can be a array or a string
     * @param string  $whence       where the data should be appended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function appendHost($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->host->append($data, $whence, $whence_index);

        return $clone;
    }

    /**
     * Remove part of the URL host component
     *
     * @param mixed $data the path data can be a array or a string
     *
     * @return self
     */
    public function removeHost($data)
    {
        $clone = clone $this;
        $clone->host->remove($data);

        return $clone;
    }

    /**
     * Set the URL path component
     *
     * @param mixed $data the host data can be a array or a string
     *
     * @return self
     */
    public function setPath($data)
    {
        $clone = clone $this;
        $clone->path->set($data);

        return $clone;
    }

    /**
     * return the URL current path
     *
     * @return \League\Url\Components\Path object
     */
    public function getPath()
    {
        return clone $this->path;
    }

    /**
     * Prepend the URL path component
     *
     * @param mixed   $data         the path data can be a array or a string
     * @param string  $whence       where the data should be prepended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function prependPath($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->path->prepend($data, $whence, $whence_index);

        return $clone;
    }

    /**
     * Append the URL path component
     *
     * @param mixed   $data         the path data can be a array or a string
     * @param string  $whence       where the data should be appended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
     */
    public function appendPath($data, $whence = null, $whence_index = null)
    {
        $clone = clone $this;
        $clone->path->append($data, $whence, $whence_index);

        return $clone;
    }

    /**
     * Remove part of the URL path component
     *
     * @param mixed $data the path data can be a array or a string
     *
     * @return self
     */
    public function removePath($data)
    {
        $clone = clone $this;
        $clone->path->remove($data);

        return $clone;
    }

    /**
     * Return a array representation of the URL
     *
     * @return array similar to PHP internal function {@link parse_url}
     */
    public function parse()
    {
        return array(
            'scheme' => $this->scheme->get(),
            'user' => $this->user->get(),
            'pass' => $this->pass->get(),
            'host' => $this->host->get(),
            'port' => $this->port->get(),
            'path' => $this->path->get(),
            'query' => $this->query->get(),
            'fragment' => $this->fragment->get(),
        );
    }
}
