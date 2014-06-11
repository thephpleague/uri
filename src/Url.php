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
 * A class to manipulate URLs
 *
 * @package League.url
 */
class Url implements EncodingInterface, UrlInterface
{
    /**
    * Scheme
    *
    * @var {@link ComponentInterface}  Object
    */
    protected $scheme;

    /**
    * User
    *
    * @var {@link ComponentInterface} Object
    */
    protected $user;

    /**
    * Pass
    *
    * @var {@link ComponentInterface} Object
    */
    protected $pass;

    /**
     * Host
     *
     * @var {@link SegmentInterface} Object
     */
    protected $host;

    /**
     * Port
     *
     *@var {@link ComponentInterface} Object
     */
    protected $port;

    /**
     * Path
     *
     * @var {@link SegmentInterface} Object
     */
    protected $path;

    /**
     * Query
     *
     * @var {@link QueryInterface} Object
     */
    protected $query;

    /**
     * Fragment
     *
     * @var {@link ComponentInterface} Object
     */
    protected $fragment;

    /**
     * The Constructor
     *
     * @param {@link ComponentInterface} $scheme   Url Scheme Component object
     * @param {@link ComponentInterface} $user     Url User Component object
     * @param {@link ComponentInterface} $pass     Url Pass Component object
     * @param {@link SegmentInterface}   $host     Url Host Component object
     * @param {@link ComponentInterface} $port     Url Port Component object
     * @param {@link SegmentInterface}   $path     Url Path Component object
     * @param {@link QueryInterface}     $query    Url Query Component object
     * @param {@link ComponentInterface} $fragment Url Fragment Component object
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
     * {@inheritdoc}
     */
    public function setEncoding($encoding_type)
    {
        $this->query->setEncoding($encoding_type);

        return $this;
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
        $this->user->set($data);

        return $this;
    }

    /**
     * get the URL user component
     *
     * @return {@link ComponentInterface}
     */
    public function getUser()
    {
        return $this->user;
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
        $this->pass->set($data);

        return $this;
    }

    /**
     * get the URL pass component
     *
     * @return {@link ComponentInterface}
     */
    public function getPass()
    {
        return $this->pass;
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
        $this->port->set($data);

        return $this;
    }

    /**
     * get the URL port component
     *
     * @return {@link ComponentInterface}
     */
    public function getPort()
    {
        return $this->port;
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
        $this->scheme->set($data);

        return $this;
    }

    /**
     * get the URL scheme component
     *
     * @return {@link ComponentInterface}
     */
    public function getScheme()
    {
        return $this->scheme;
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
        $this->fragment->set($data);

        return $this;
    }

    /**
     * get the URL fragment component
     *
     * @return {@link ComponentInterface}
     */
    public function getFragment()
    {
        return $this->fragment;
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
        $this->query->set($data);

        return $this;
    }

    /**
     * get the URL user component
     *
     * @return {@link QueryInterface}
     */
    public function getQuery()
    {
        return $this->query;
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
        $this->host->set($data);

        return $this;
    }

    /**
     * get the URL host component
     *
     * @return {@link SegmentInterface}
     */
    public function getHost()
    {
        return $this->host;
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
        $this->path->set($data);

        return $this;
    }

    /**
     * get the URL path component
     *
     * @return {@link SegmentInterface}
     */
    public function getPath()
    {
        return $this->path;
    }
}
