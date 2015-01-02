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

use League\Url\Interfaces\UrlInterface;
use League\Url\Traits\Factory;
use RuntimeException;

/**
 * A Immutable Value Object class to manipulate URLs
 *
 *  @package League.url
 *  @since  3.0.0
 */
final class Url implements UrlInterface
{
    /**
    * Scheme
    *
    * @var \League\Url\Scheme
    */
    private $scheme;

    /**
    * User
    *
    * @var \League\Url\User
    */
    private $user;

    /**
    * Pass
    *
    * @var \League\Url\Pass
    */
    private $pass;

    /**
     * Host
     *
     * @var \League\Url\Host
     */
    private $host;

    /**
     * Port
     *
     *@var \League\Url\Port
     */
    private $port;

    /**
     * Path
     *
     * @var \League\Url\Path
     */
    private $path;

    /**
     * Query
     *
     * @var \League\Url\Query
     */
    private $query;

    /**
     * Fragment
     *
     * @var \League\Url\Fragment
     */
    private $fragment;

    use Factory;

    /**
     * create a new instance of URL
     *
     * @param  array $components a array created from PHP parse_url function
     */
    public function __construct(array $components = [])
    {
        $components = array_merge([
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ], $components);

        $this->scheme = new Scheme($components['scheme']);
        $this->user = new User($components['user']);
        $this->pass = new Pass($components['pass']);
        $this->host = new Host($components['host']);
        $this->port = new Port($components['port']);
        $this->path = new Path($components['path']);
        $this->query = new Query($components['query']);
        $this->fragment = new Fragment($components['fragment']);
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
     * Array representation of an URL
     *
     * @return array
     */
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
    public function getUserInfo()
    {
        $user = $this->user->getUriComponent().$this->pass->getUriComponent();
        if ('' != $user) {
            $user .= '@';
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $user = $this->getUserInfo();
        $host = $this->host->getUriComponent();
        $port = $this->port->getUriComponent();

        return $user.$host.$port;
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

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UrlInterface $url)
    {
        return $this->__toString() == $url->__toString();
    }

    /**
     * Set the URL scheme component
     *
     * @param string $data
     *
     * @return static
     */
    public function withScheme($data)
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
     * Set the URL user component
     *
     * @param string $data
     *
     * @return static
     */
    public function withUser($data)
    {
        $clone = clone $this;
        $clone->user->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
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
     * @return static
     */
    public function withPass($data)
    {
        $clone = clone $this;
        $clone->pass->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getPass()
    {
        return clone $this->pass;
    }

    /**
     * Set the URL host component
     *
     * @param string|array|\Traversable $data
     *
     * @return static
     */
    public function withHost($data)
    {
        $clone = clone $this;
        $clone->host->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return clone $this->host;
    }

    /**
     * Set the URL port component
     *
     * @param string|integer $data
     *
     * @return static
     */
    public function withPort($data)
    {
        $clone = clone $this;
        $clone->port->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return clone $this->port;
    }

    /**
     * Set the URL path component
     *
     * @param string|array|\Traversable $data
     *
     * @return static
     */
    public function withPath($data)
    {
        $clone = clone $this;
        $clone->path->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return clone $this->path;
    }

    /**
     * Set the URL query component
     *
     * @param string|array|\Traversable $data
     *
     * @return static
     */
    public function withQuery($data)
    {
        $clone = clone $this;
        $clone->query->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
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
     * @return static
     */
    public function withFragment($data)
    {
        $clone = clone $this;
        $clone->fragment->set($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return clone $this->fragment;
    }
}
