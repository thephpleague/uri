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

use RuntimeException;
use League\Url\Components\Scheme;
use League\Url\Components\Component;
use League\Url\Components\Host;
use League\Url\Components\Port;
use League\Url\Components\Path;
use League\Url\Components\Query;

/**
 * A Immutable Class to manipulate URLs
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
     * The constructor
     *
     * @param mixed   $url           an URL as a string or
     *                               as an object that implement the __toString method
     * @param integer $encoding_type the RFC to follow when encoding the query string
     *
     * @throws RuntimeException If the URL can not be parse
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

        //Url reconstruction
        if (!empty($scheme)) {
            $scheme .= ':';
        }

        if (!empty($pass)) {
            $pass = ':'.$pass;
        }
        $user .= $pass;
        if (!empty($user)) {
            $user .='@';
        }

        if (!empty($port)) {
            $port = ':'.$port;
        }

        if (!empty($query)) {
            $query = '?'.$query;
        }
        if (!empty($fragment)) {
            $fragment = '#'.$fragment;
        }

        if (!empty($host) || !empty($scheme)) {
            $scheme .= '//';
        }

        $domain = $scheme.$user.$host.$port;
        if (!empty($path) || !empty($domain)) {
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
     * @return string
     */
    public function getUser()
    {
        return $this->user->get();
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
     * @return string
     */
    public function getPass()
    {
        return $this->pass->get();
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
     * @return integer
     */
    public function getPort()
    {
        return $this->port->get();
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
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme->get();
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
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment->get();
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
        return $this->query->getEncodingType();
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
     * @return string
     */
    public function getQuery()
    {
        return $this->query->get();
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
     * @return string
     */
    public function getHost()
    {
        return $this->host->get();
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
     * @return string
     */
    public function getPath()
    {
        return $this->path->get();
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
