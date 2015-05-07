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

use InvalidArgumentException;
use League\Url\Interfaces;
use League\Url\Util;
use Psr\Http\Message\UriInterface;
use ReflectionClass;

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
     * @var Scheme
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
     * Interfaces\Host Component
     *
     * @var Host
     */
    protected $host;

    /**
     * Port Component
     *
     * @var Port
     */
    protected $port;

    /**
     * Interfaces\Path Component
     *
     * @var Path
     */
    protected $path;

    /**
     * Interfaces\Query Component
     *
     * @var Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var League\Url\Fragment
     */
    protected $fragment;

    /**
     * Standard Port for known schemes
     *
     * @var array
     */
    protected static $standardPorts = [
        'ftp'   => [21  => 1],
        'ftps'  => [990 => 1, 989 => 1],
        'https' => [443 => 1],
        'http'  => [80  => 1],
        'ldap'  => [389 => 1],
        'ldaps' => [636 => 1],
        'ssh'   => [22  => 1],
        'ws'    => [80  => 1],
        'wss'   => [443 => 1],
    ];

    /**
     * A Factory trait fetch info from Server environment variables
     */
    use Util\ServerInfo;

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
        Interfaces\Host $host,
        Port $port,
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
     * Create a new League\Url\Url instance from an array returned by
     * PHP parse_url function
     *
     * @param array $components
     *
     * @return static
     */
    public static function createFromComponents(array $components)
    {
        $components += [
            "scheme" => null, "user" => null, "pass" => null, "host" => null,
            "port" => null, "path" => null, "query" => null, "fragment" => null
        ];

        $url = (new ReflectionClass('League\Url\Url'))->newInstanceWithoutConstructor();
        $url->scheme   = new Scheme($components["scheme"]);
        $url->user     = new User($components["user"]);
        $url->pass     = new Pass($components["pass"]);
        $url->host     = new Host($components["host"]);
        $url->port     = new Port($components["port"]);
        $url->path     = new Path($components["path"]);
        $url->query    = new Query($components["query"]);
        $url->fragment = new Fragment($components["fragment"]);

        return $url;
    }

    /**
     * Create a new League\Url\Url instance from a string
     *
     * @param  string $url
     *
     * @throws \InvalidArgumentException If the URL can not be parsed
     *
     * @return static
     */
    public static function createFromUrl($url)
    {
        $url = trim($url);
        $components = @parse_url($url);
        if (false === $components) {
            throw new InvalidArgumentException(sprintf("The given URL: `%s` could not be parse", $url));
        }

        return static::createFromComponents($components);
    }

    /**
     * Create a new League\Url\Url object from the environment
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @throws \InvalidArgumentException If the URL can not be parsed
     *
     * @return static
     */
    public static function createFromServer(array $server)
    {
        return static::createFromUrl(
            static::fetchServerScheme($server).'//'
            .static::fetchServerUserInfo($server)
            .static::fetchServerHost($server)
            .static::fetchServerPort($server)
            .static::fetchServerRequestUri($server)
        );
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
        if ($value->sameValueAs($this->$name)) {
            return $this;
        }
        $clone = clone $this;
        $clone->$name = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return ! is_null($this->scheme->get());
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
        return $this->withComponent('path', $this->path->normalize());
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
    public function getAuthority()
    {
        if (! count($this->host)) {
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
    public function hasStandardPort()
    {
        $port = $this->port->get();
        if (empty($port)) {
            return true;
        }

        $scheme = $this->scheme->get();
        return isset(
            $scheme,
            static::$standardPorts[$scheme],
            static::$standardPorts[$scheme][$port]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $host = $this->host->__toString();
        if (empty($host)) {
            $host = null;
        }

        return [
            'scheme'   => $this->scheme->get(),
            'user'     => $this->user->get(),
            'pass'     => $this->pass->get(),
            'host'     => $host,
            'port'     => $this->port->get(),
            'path'     => $this->path->get(),
            'query'    => $this->query->get(),
            'fragment' => $this->fragment->get(),
        ];
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
    public function sameValueAs(UriInterface $url)
    {
        return $url->__toString() == $this->__toString();
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
        if ('' != $rel->path->get()) {
            return $this->resolvePath($res, $rel);
        }

        if ('' != $rel->query->get()) {
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
                '' == $url->path->get() || $url->path->isAbsolute()
            );
        }

        return $url->withPath($path->normalize())->withQuery($rel->query);
    }
}
