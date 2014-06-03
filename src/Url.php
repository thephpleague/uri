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

use League\Url\Interfaces\QueryInterface;
use League\Url\Interfaces\SegmentInterface;
use League\Url\Interfaces\ComponentInterface;
use League\Url\Interfaces\EncodingInterface;

/**
 * A Immutable Value Object class to manipulate URLs
 *
 * @package League.url
 */
final class Url implements EncodingInterface
{
    /**
    * Scheme
    *
    * @var{@link ComponentInterface}  Object
    */
    private $scheme;

    /**
    * User
    *
    * @var {@link ComponentInterface} Object
    */
    private $user;

    /**
    * Pass
    *
    * @var {@link ComponentInterface} Object
    */
    private $pass;

    /**
     * Host
     *
     * @var {@link SegmentInterface} Object
     */
    private $host;

    /**
     * Port
     *
     *@var {@link ComponentInterface} Object
     */
    private $port;

    /**
     * Path
     *
     * @var {@link SegmentInterface} Object
     */
    private $path;

    /**
     * Query
     *
     * @var {@link QueryInterface} Object
     */
    private $query;

    /**
     * Fragment
     *
     * @var {@link ComponentInterface} Object
     */
    private $fragment;

    /**
     * The Constructor
     *
     * @param {@link ComponentInterface} $scheme   Url Scheme object
     * @param {@link ComponentInterface} $user     Url Component object
     * @param {@link ComponentInterface} $pass     Url Component object
     * @param {@link SegmentInterface}   $host     Url Host object
     * @param {@link ComponentInterface} $port     Url Port object
     * @param {@link SegmentInterface}   $path     Url Path object
     * @param {@link QueryInterface}     $query    Url Query object
     * @param {@link ComponentInterface} $fragment Url Component object
     */
    public function __construct(
        ComponentInterface $scheme,
        ComponentInterface $user,
        ComponentInterface $pass,
        SegmentInterface $host,
        ComponentInterface $port,
        SegmentInterface $path,
        QueryInterface $query,
        ComponentInterface $fragment
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
        $scheme = $this->scheme->getUriComponent();
        $user = $this->user->getUriComponent();
        $pass = $this->pass->getUriComponent();
        $host = $this->host->getUriComponent();
        $port = $this->port->getUriComponent();
        $path = $this->path->getUriComponent();
        $query = $this->query->getUriComponent();
        $fragment = $this->fragment->getUriComponent();

        $user .= $pass;
        if ('' != $user) {
            $user .= '@';
        }

        if ('' != $host && '' == $scheme) {
            $scheme = '//';
        }

        $domain = $scheme.$user.$host.$port;
        if ('' == $path && '' != $domain) {
            $path = '/';
        }

        return $domain.$path.$query.$fragment;
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

    /**
     * Compare two Url object and tells whether they can be considered equal
     *
     * @param \League\Url\Url $url
     *
     * @return boolean
     */
    public function sameValueAs(Url $url, $strict = false)
    {
        if (! $strict) {
            $this_url = $this->setEncodingType(Url::PHP_QUERY_RFC1738)->__toString();
            $that_url = $url->setEncodingType(Url::PHP_QUERY_RFC1738)->__toString();

            return  $this_url == $that_url;
        }

        return $this->__toString() == $url->__toString();
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
     * @return {@link Component}
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
     * @return {@link Component}
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
     * @return {@link Port}
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
     * @return {@link Scheme}
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
     * @return {@link Component}
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
     * @return {@link Query}
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
     * @return {@link Host}
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
     * @return {@link Path}
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
}
