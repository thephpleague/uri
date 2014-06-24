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
use League\Url\Components\User;
use League\Url\Components\Pass;
use League\Url\Components\Host;
use League\Url\Components\Port;
use League\Url\Components\Path;
use League\Url\Components\Query;
use League\Url\Components\Fragment;

/**
 * A Immutable Value Object class to manipulate URLs
 *
 *  @package League.url
 *  @since  3.0.0
 */
class UrlImmutable extends AbstractUrl
{
    /**
     * The Constructor
     * @param Scheme   $scheme   The URL Scheme component
     * @param User     $user     The URL User component
     * @param Pass     $pass     The URL Pass component
     * @param Host     $host     The URL Host component
     * @param Port     $port     The URL Port component
     * @param Path     $path     The URL Path component
     * @param Query    $query    The URL Query component
     * @param Fragment $fragment The URL Fragment component
     */
    public function __construct(
        Scheme $scheme,
        User $user,
        Pass $pass,
        Host $host,
        Port $port,
        Path $path,
        Query $query,
        Fragment $fragment
    ) {
        $this->scheme = clone $scheme;
        $this->user = clone $user;
        $this->pass = clone $pass;
        $this->host = clone $host;
        $this->port = clone $port;
        $this->path = clone $path;
        $this->query = clone $query;
        $this->fragment = clone $fragment;
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
     * Set the URL scheme component
     *
     * @param mixed $data
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
     * get the URL scheme component
     *
     * @return {@link ComponentInterface}
     */
    public function getScheme()
    {
        return clone $this->scheme;
    }

    /**
     * Set the URL user component
     *
     * @param mixed $data
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
     * @param mixed $data
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
     * Set the URL host component
     *
     * @param mixed $data
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
     * Set the URL port component
     *
     * @param mixed $data
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
     * Set the URL path component
     *
     * @param mixed $data
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

    /**
     * Set the URL query component
     *
     * @param mixed $data
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
}
