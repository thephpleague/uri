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

use League\Url\Interfaces;
use League\Url\Util;
use Psr\Http\Message\UriInterface;

/**
* Value object representing a URL.
*
* @package League.url
* @since 1.0.0
*/
class Url implements Interfaces\Url
{
    /**
     * Scheme Component
     *
     * @var Interfaces\Scheme
     */
    protected $scheme;

    /**
     * User Component
     *
     * @var User
     */
    protected $user;

    /**
     * Pass Component
     *
     * @var Pass
     */
    protected $pass;

    /**
     * Host Component
     *
     * @var Interfaces\Host
     */
    protected $host;

    /**
     * Port Component
     *
     * @var Interfaces\Port
     */
    protected $port;

    /**
     * Path Component
     *
     * @var Interfaces\Path
     */
    protected $path;

    /**
     * Query Component
     *
     * @var Interfaces\Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var Fragment
     */
    protected $fragment;

    /**
     * A Factory trait fetch info from Server environment variables
     */
    use Util\ServerInfo;


    /**
     * A Factory Trait to create new URL instance
     */
    use Util\UrlFactory;

    /**
     * Create a new instance of URL
     *
     * @param Interfaces\Scheme $scheme
     * @param User              $user
     * @param Pass              $pass
     * @param Interfaces\Host   $host
     * @param Interfaces\Port   $port
     * @param Interfaces\Path   $path
     * @param Interfaces\Query  $query
     * @param Fragment          $fragment
     */
    public function __construct(
        Interfaces\Scheme $scheme,
        User $user,
        Pass $pass,
        Interfaces\Host $host,
        Interfaces\Port $port,
        Interfaces\Path $path,
        Interfaces\Query $query,
        Fragment $fragment
    ) {
        $this->scheme   = clone $scheme;
        $this->user     = clone $user;
        $this->pass     = clone $pass;
        $this->host     = clone $host;
        $this->port     = clone $port;
        $this->path     = clone $path;
        $this->query    = clone $query;
        $this->fragment = clone $fragment;
    }

    /**
     * clone the current instance
     */
    public function __clone()
    {
        $this->scheme   = clone $this->scheme;
        $this->user     = clone $this->user;
        $this->pass     = clone $this->pass;
        $this->host     = clone $this->host;
        $this->port     = clone $this->port;
        $this->path     = clone $this->path;
        $this->query    = clone $this->query;
        $this->fragment = clone $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_map(function ($value) {
            if (empty($value)) {
                return null;
            }
            return $value;
        }, [
            'scheme'   => $this->scheme->__toString(),
            'user'     => $this->user->__toString(),
            'pass'     => $this->pass->__toString(),
            'host'     => $this->host->__toString(),
            'port'     => $this->port->toInt(),
            'path'     => $this->path->__toString(),
            'query'    => $this->query->__toString(),
            'fragment' => $this->fragment->__toString(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $auth = $this->getAuthority();
        if (! empty($auth)) {
            $auth = '//'.$auth;
        }

        return $this->scheme->getUriComponent().$auth
            .$this->path->getUriComponent()
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        if ($this->host->isEmpty()) {
            return '';
        }

        $userinfo = $this->getUserInfo();
        if (! empty($userinfo)) {
            $userinfo .= '@';
        }

        $port = '';
        if (! $this->hasStandardPort()) {
            $port = $this->port->getUriComponent();
        }

        return $userinfo.$this->host->getUriComponent().$port;
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return ! $this->scheme->isEmpty() && '' != $this->getAuthority() && $this->path->isAbsolute();
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort()
    {
        return $this->scheme->hasStandardPort($this->port);
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
    public function getScheme()
    {
        return clone $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        return $this->withComponent('scheme', $scheme);
    }

    /**
     * Returns an instance with the modified component
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param string $name  the component to set
     * @param string $value the component value
     *
     * @return static
     */
    protected function withComponent($name, $value)
    {
        $value = $this->$name->withValue($value);
        if ($this->$name->sameValueAs($value)) {
            return $this;
        }
        $clone = clone $this;
        $clone->$name = $value;

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
     * {@inheritdoc}
     */
    public function getPass()
    {
        return clone $this->pass;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        $info = $this->user->getUriComponent();
        if (empty($info)) {
            return '';
        }

        return $info.$this->pass->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $pass = null)
    {
        return $this->withComponent('user', $user)
                    ->withComponent('pass', $pass);
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return clone $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        return $this->withComponent('host', $host);
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return clone $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        return $this->withComponent('port', $port);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return clone $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        return $this->withComponent('path', $path);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize()
    {
        return $this->withComponent('path', $this->path->withoutDotSegments());
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return clone $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        return $this->withComponent('query', $query);
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return clone $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        return $this->withComponent('fragment', $fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($url)
    {
        $rel = static::createFromUrl($url);
        if ($rel->isAbsolute()) {
            return $rel->normalize();
        }

        $auth = $rel->getAuthority();
        if (! empty($auth) && $auth != $this->getAuthority()) {
            return $rel->withScheme($this->scheme)->normalize();
        }

        $res = $this->withFragment($rel->fragment);
        if (! $rel->path->isEmpty()) {
            return $this->resolvePath($res, $rel);
        }

        if (! $rel->query->isEmpty()) {
            return $res->withQuery($rel->query)->normalize();
        }

        return $res->normalize();
    }

    /**
     * returns the resolve URL components
     *
     * @param Url $url the final URL
     * @param Url $rel the relative URL
     *
     * @return static
     */
    protected function resolvePath(Url $url, Url $rel)
    {
        $path = $rel->path;
        if (! $rel->path->isAbsolute()) {
            $segments = $url->path->toArray();
            array_pop($segments);
            $path = Path::createFromArray(
                array_merge($segments, $rel->path->toArray()),
                $url->path->isEmpty() || $url->path->isAbsolute()
            );
        }

        return $url->withPath($path->withoutDotSegments())->withQuery($rel->query);
    }
}
