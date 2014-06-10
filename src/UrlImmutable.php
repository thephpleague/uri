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

/**
 * A Immutable Value Object class to manipulate URLs
 *
 * @package League.url
 */
final class UrlImmutable extends AbstractUrl
{
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
    public function setEncodingType($encoding_type)
    {
        $clone = clone $this;
        $clone->query->setEncodingType($encoding_type);

        return $clone;
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
     * get the URL user component
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
     * Return the current URL pass component
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
     * @return {@link ComponentInterface}
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
     * @return {@link ComponentInterface}
     */
    public function getScheme()
    {
        return clone $this->scheme;
    }

    /**
     * Set the URL Fragment component
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
     * return the URL fragment component
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
     * @return {@link QueryInterface}
     */
    public function getQuery()
    {
        return clone $this->query;
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
     * @return {@link ComponentSegmentInterface}
     */
    public function getHost()
    {
        return clone $this->host;
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
     * @return {@link ComponentSegmentInterface}
     */
    public function getPath()
    {
        return clone $this->path;
    }
}
