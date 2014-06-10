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
     * {@inheritdoc}
     */
    public function setUser($data)
    {
        $this->user->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setPass($data)
    {
        $this->pass->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * {@inheritdoc}
     */
    public function setPort($data)
    {
        $this->port->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function setScheme($data)
    {
        $this->scheme->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function setFragment($data)
    {
        $this->fragment->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodingType($encoding_type)
    {
        $this->query->setEncodingType($encoding_type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($data)
    {
        $this->query->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost($data)
    {
        $this->host->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($data)
    {
        $this->path->set($data);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }
}
