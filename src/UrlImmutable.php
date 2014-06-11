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

use League\Url\Interfaces\EncodingInterface;
use League\Url\Interfaces\UrlInterface;
use League\Url\Interfaces\QueryInterface;
use League\Url\Interfaces\SegmentInterface;
use League\Url\Interfaces\ComponentInterface;

/**
 * A Immutable Value Object class to manipulate URLs
 *
 * @package League.url
 */
final class UrlImmutable implements EncodingInterface, UrlInterface
{
    /**
    * Scheme
    *
    * @var {@link ComponentInterface}  Object
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
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getBaseUrl().$this->getRelativeUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeUrl()
    {
        $path = $this->path->getUriComponent();
        $query = $this->query->getUriComponent();
        $fragment = $this->fragment->getUriComponent();
        if ('' == $path) {
            $path = '/'.$path;
        }

        return $path.$query.$fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        $scheme = $this->scheme->getUriComponent();
        $user = $this->user->getUriComponent();
        $pass = $this->pass->getUriComponent();
        $host = $this->host->getUriComponent();
        $port = $this->port->getUriComponent();

        $user .= $pass;
        if ('' != $user) {
            $user .= '@';
        }

        if ('' != $host && '' == $scheme) {
            $scheme = '//';
        }

        return $scheme.$user.$host.$port;
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UrlInterface $url)
    {
        $this_url = clone $this;
        $that_url = clone $url;
        $this_url = $this_url->setEncoding(PHP_QUERY_RFC1738);
        $that_url = $that_url->setEncoding(PHP_QUERY_RFC1738);

        return  $this_url->__toString() == $that_url->__toString();
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
     * {@inheritdoc}
     */
    public function setEncoding($encoding_type)
    {
        $clone = clone $this;
        $clone->query->setEncoding($encoding_type);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncoding()
    {
        return $this->query->getEncoding();
    }

    /**
     * Set the URL user component
     *
     * @param string $data
     *
     * @return self
     */
    public function setUser($data)
    {
        $clone = clone $this;
        $clone->user->set($data);

        return $clone;
    }

    /**
     * get the URL pass component
     *
     * @return {@link ComponentInterface}
     */
    public function getUser()
    {
        return clone $this->user;
    }

    /**
     * Set the URL pass component
     *
     * @param string $data
     *
     * @return self
     */
    public function setPass($data)
    {
        $clone = clone $this;
        $clone->pass->set($data);

        return $clone;
    }

    /**
     * get the URL pass component
     *
     * @return {@link ComponentInterface}
     */
    public function getPass()
    {
        return clone $this->pass;
    }

    /**
     * Set the URL port component
     *
     * @param string $data
     *
     * @return self
     */
    public function setPort($data)
    {
        $clone = clone $this;
        $clone->port->set($data);

        return $clone;
    }

    /**
     * get the URL pass component
     *
     * @return {@link ComponentInterface}
     */
    public function getPort()
    {
        return clone $this->port;
    }

    /**
     * Set the URL scheme component
     *
     * @param string $data
     *
     * @return self
     */
    public function setScheme($data)
    {
        $clone = clone $this;
        $clone->scheme->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return clone $this->scheme;
    }

    /**
     * Set the URL fragment component
     *
     * @param string $data
     *
     * @return self
     */
    public function setFragment($data)
    {
        $clone = clone $this;
        $clone->fragment->set($data);

        return $clone;
    }

    /**
     * get the URL pass component
     *
     * @return {@link ComponentInterface}
     */
    public function getFragment()
    {
        return clone $this->fragment;
    }

    /**
     * Set the URL query component
     *
     * @param string $data
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
     * get the URL pass component
     *
     * @return {@link QueryInterface}
     */
    public function getQuery()
    {
        return clone $this->query;
    }

    /**
     * Set the URL host component
     *
     * @param string $data
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
     * get the URL pass component
     *
     * @return {@link SegmentInterface}
     */
    public function getHost()
    {
        return clone $this->host;
    }

    /**
     * Set the URL path component
     *
     * @param string $data
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
     * get the URL pass component
     *
     * @return {@link SegmentInterface}
     */
    public function getPath()
    {
        return clone $this->path;
    }
}
