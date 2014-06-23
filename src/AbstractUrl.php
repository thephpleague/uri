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

use RuntimeException;
use League\Url\Components\ComponentInterface;
use League\Url\Components\QueryInterface;
use League\Url\Components\SegmentInterface;
use League\Url\Components\Scheme;
use League\Url\Components\User;
use League\Url\Components\Pass;
use League\Url\Components\Host;
use League\Url\Components\Port;
use League\Url\Components\Path;
use League\Url\Components\Query;
use League\Url\Components\Fragment;

/**
 * A Factory to ease League\Url\Url Object instantiation
 *
 *  @package League.url
 *  @since  3.0.0
 */
abstract class AbstractUrl implements UrlInterface
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
     * @param ComponentInterface $scheme   The URL Scheme component
     * @param ComponentInterface $user     The URL User component
     * @param ComponentInterface $pass     The URL Pass component
     * @param SegmentInterface   $host     The URL Host component
     * @param ComponentInterface $port     The URL Port component
     * @param SegmentInterface   $path     The URL Path component
     * @param QueryInterface     $query    The URL Query component
     * @param ComponentInterface $fragment The URL Fragment component
     */
    protected function __construct(
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
        $url = $this->getBaseUrl().$this->getRelativeUrl();
        if ('/' == $url) {
            return '';
        }

        return $url;
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
        $this_url = self::createFromUrl($this);
        $that_url = self::createFromUrl($url);

        return $this_url->__toString() == $that_url->__toString();
    }

    /**
     * Return a instance of Url from a string
     *
     * @param string $url a string or an object that implement the __toString method
     *
     * @return AbstractUrl
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromUrl($url)
    {
        $url = (string) $url;
        $url = trim($url);
        if (false === ($components = @parse_url($url))) {
            throw new RuntimeException(sprintf('The given URL: `%s` could not be parse', $url));
        }

        $components = self::sanitizeComponents($components);

        return new static(
            new Scheme($components['scheme']),
            new User($components['user']),
            new Pass($components['pass']),
            new Host($components['host']),
            new Port($components['port']),
            new Path($components['path']),
            new Query($components['query']),
            new Fragment($components['fragment'])
        );
    }

    /**
     * Return a instance of Url from a server array
     *
     * @param array $server the server array
     *
     * @return AbstractUrl
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromServer(array $server)
    {
        $scheme = self::fetchServerScheme($server);
        $host = self::fetchServerHost($server);
        $port = self::fetchServerPort($server);
        $request = self::fetchServerRequestUri($server);

        return self::createFromUrl($scheme.$host.$port.$request);
    }

    /**
     * Return the Server URL scheme component
     *
     * @param array $server the server array
     *
     * @return string
     */
    protected static function fetchServerScheme(array $server)
    {
        $scheme = '';
        if (isset($server['SERVER_PROTOCOL'])) {
            $scheme = explode('/', $server['SERVER_PROTOCOL']);
            $scheme = strtolower($scheme[0]);
            if (isset($server['HTTPS']) && 'off' != $server['HTTPS']) {
                $scheme .= 's';
            }
            $scheme .= ':';
        }

        return $scheme.'//';
    }

    /**
     * Return the Server URL host component
     *
     * @param array $server the server array
     *
     * @return string
     *
     * @throws \RuntimeException If no host is detected
     */
    protected static function fetchServerHost(array $server)
    {
        if (isset($server['HTTP_HOST'])) {
            return $server['HTTP_HOST'];
        } elseif (isset($server['SERVER_ADDR'])) {
            return $server['SERVER_ADDR'];
        }

        throw new RuntimeException('Host could not be detected');
    }

    /**
     * Return the Server URL port component
     *
     * @param array $server the server array
     *
     * @return string
     */
    protected static function fetchServerPort(array $server)
    {
        $port = '';
        if (isset($server['SERVER_PORT']) && '80' != $server['SERVER_PORT']) {
            $port = ':'. (int) $server['SERVER_PORT'];
        }

        return $port;
    }

    /**
     * Return the Server URL Request Uri component
     *
     * @param array $server the server array
     *
     * @return string
     */
    protected static function fetchServerRequestUri(array $server)
    {
        if (isset($server['REQUEST_URI'])) {
            return $server['REQUEST_URI'];
        } elseif (isset($server['PHP_SELF'])) {
            return $server['PHP_SELF'];
        }

        return '/';
    }

    /**
     * Sanitize URL components
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function sanitizeComponents(array $components)
    {
        $components = array_merge(array(
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ), $components);

        $components = self::formatAuthComponent($components);

        return self::formatPathComponent($components);
    }

    /**
     * Reformat the component according to the auth content
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function formatAuthComponent(array $components)
    {
        if (!is_null($components['scheme'])
            && is_null($components['host'])
            && !empty($components['path'])
            && strpos($components['path'], '@') !== false
        ) {
            $tmp = explode('@', $components['path'], 2);
            $components['user'] = $components['scheme'];
            $components['pass'] = $tmp[0];
            $components['path'] = $tmp[1];
            $components['scheme'] = null;
        }

        return $components;
    }

    /**
     * Reformat the component according to the host content
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function formatHostComponent(array $components)
    {
        if (strpos($components['host'], '@')) {
            list($auth, $components['host']) = explode('@', $components['host']);
            $components['user'] = $auth;
            $components['pass'] = null;
            if (false !== strpos($auth, ':')) {
                list($components['user'], $components['pass']) = explode(':', $auth);
            }
        }

        return $components;
    }

    /**
     * Reformat the component according to the path content
     *
     * @param array $components the result from parse_url
     *
     * @return array
     */
    protected static function formatPathComponent(array $components)
    {
        if (is_null($components['scheme'])
            && is_null($components['host'])
            && !empty($components['path'])
        ) {
            $tmp = $components['path'];
            if (0 === strpos($tmp, '//')) {
                $tmp = substr($tmp, 2);
            }
            $components['path'] = null;
            $res = explode('/', $tmp, 2);
            $components['host'] = $res[0];
            if (isset($res[1])) {
                $components['path'] = $res[1];
            }
            $components = self::formatHostComponent($components);
        }

        return $components;
    }
}
