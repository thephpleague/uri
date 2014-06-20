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
 * A class to manipulate URLs
 *
 * @package League.url
 */
class Url extends AbstractUrl
{
    /**
     * Set the URL scheme component
     *
     * @param mixed $data
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
     * Set the URL user component
     *
     * @param mixed $data
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
     * @param mixed $data
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
     * Set the URL host component
     *
     * @param mixed $data
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
     * Set the URL port component
     *
     * @param mixed $data
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
     * Set the URL path component
     *
     * @param mixed $data
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

    /**
     * Set the URL query component
     *
     * @param mixed $data
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
     * Set the URL fragment component
     *
     * @param mixed $data
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
}
