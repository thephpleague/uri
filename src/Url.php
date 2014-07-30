<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.2.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use League\Url\Components\Fragment;
use League\Url\Components\HostInterface;
use League\Url\Components\Pass;
use League\Url\Components\PathInterface;
use League\Url\Components\Port;
use League\Url\Components\QueryInterface;
use League\Url\Components\Scheme;
use League\Url\Components\User;

/**
 * A class to manipulate URLs
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Url extends AbstractUrl
{
    /**
     * The Constructor
     * @param Scheme         $scheme   The URL Scheme component
     * @param User           $user     The URL User component
     * @param Pass           $pass     The URL Pass component
     * @param HostInterface  $host     The URL Host component
     * @param Port           $port     The URL Port component
     * @param PathInterface  $path     The URL Path component
     * @param QueryInterface $query    The URL Query component
     * @param Fragment       $fragment The URL Fragment component
     */
    protected function __construct(
        Scheme $scheme,
        User $user,
        Pass $pass,
        HostInterface $host,
        Port $port,
        PathInterface $path,
        QueryInterface $query,
        Fragment $fragment
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
}
