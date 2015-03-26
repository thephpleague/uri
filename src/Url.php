<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use League\Url\Interfaces\Url as UrlInterface;
use Psr\Http\Message\UriInterface;

/**
* A class to manipulate an URL as a Value Object
*
* @package League.url
* @since 1.0.0
*/
class Url implements UrlInterface
{
    /**
     * Scheme Component
     *
     * @var League\Url\Scheme
     */
    protected $scheme;

    /**
     * User Component
     *
     * @var League\Url\User
     */
    protected $user;

    /**
     * Pass Component
     *
     * @var League\Url\Pass
     */
    protected $pass;

    /**
     * Host Component
     *
     * @var League\Url\Host
     */
    protected $host;

    /**
     * Port Component
     *
     * @var League\Url\Port
     */
    protected $port;

    /**
     * Path Component
     *
     * @var League\Url\Path
     */
    protected $path;

    /**
     * Query Component
     *
     * @var League\Url\Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var League\Url\Fragment
     */
    protected $fragment;

    /**
     * Standard Port for known scheme
     *
     * @var array
     */
    protected static $standardPorts = [
        'https' => 443,
        'http'  => 80,
        'ftp'   => 21,
        'ftps'  => 990,
        'ws'    => 80,
        'wss'   => 443,
        'ssh'   => 22,
    ];

    /**
     * A Factory trait to create new URL object more easily
     */
    use UrlFactoryTrait;

    /**
     * Create a new instance of URL
     *
     * @param Scheme   $scheme
     * @param User     $user
     * @param Pass     $pass
     * @param Host     $host
     * @param Port     $port
     * @param Path     $path
     * @param Query    $query
     * @param Fragment $fragment
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
     * clone the current instance
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
    public function withScheme($scheme)
    {
        $clone = clone $this;
        $clone->scheme = new Scheme($scheme);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $pass = null)
    {
        $clone = clone $this;
        $clone->user = $this->user->withValue($user);
        $clone->pass = $this->pass->withValue($pass);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = new Host($host);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $clone = clone $this;
        $clone->port = $this->port->withValue($port);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $clone->path = new Path($path);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $clone = clone $this;
        $clone->query = new Query($query);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $clone = clone $this;
        $clone->fragment = $this->fragment->withValue($fragment);

        return $clone;
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
    public function getUser()
    {
        return $this->user;
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
    public function getHost()
    {
        return $this->host;
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
    public function getPath()
    {
        return $this->path;
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
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        $info = $this->user->getUriComponent();
        if (empty($info)) {
            return $info;
        }

        return $info.$this->pass->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $host = $this->host->getUriComponent();
        if (empty($host)) {
            return '';
        }

        $userinfo = $this->getUserInfo();
        if (strpos($userinfo, ':') !== false) {
            $userinfo .= '@';
        }

        $scheme  = $this->scheme->get();
        $thePort = $this->port->getUriComponent();
        if (empty($thePort)
            || is_null($scheme)
            || (isset(static::$standardPorts[$scheme]) && $this->port->get() == static::$standardPorts[$scheme])
        ) {
            $thePort = '';
        }

        return $userinfo.$host.$thePort;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        $scheme = $this->scheme->getUriComponent();
        $auth = $this->getAuthority();
        if ('' != $auth && '' == $scheme) {
            $scheme = '//';
        }

        return $scheme.$auth;
    }

    public function toArray()
    {
        return [
            'scheme' => $this->scheme->get(),
            'user' => $this->user->get(),
            'pass' => $this->pass->get(),
            'host' => $this->host->get(),
            'port' => $this->port->get(),
            'path' => $this->path->get(),
            'query' => $this->query->get(),
            'fragment' => $this->fragment->get(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $url = $this->getBaseUrl()
            .$this->path->getUriComponent()
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
        if ('/' == $url) {
            return '';
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UriInterface $url)
    {
        return $url->__toString() == $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function normalize()
    {
        $clone = clone $this;
        $clone->path = $this->path->normalize();

        return $clone;
    }
}
