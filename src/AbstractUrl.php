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

use League\Url\Interfaces\UrlInterface;
use RuntimeException;

/**
 * A Factory to ease League\Url\Url Object instantiation
 *
 *  @package League.url
 *  @since  3.0.0
 */
abstract class AbstractUrl
{
    /**
    * Scheme
    *
    * @var \League\Url\Scheme
    */
    protected $scheme;

    /**
    * User
    *
    * @var \League\Url\User
    */
    protected $user;

    /**
    * Pass
    *
    * @var \League\Url\Pass
    */
    protected $pass;

    /**
     * Host
     *
     * @var \League\Url\Host
     */
    protected $host;

    /**
     * Port
     *
     *@var \League\Url\Port
     */
    protected $port;

    /**
     * Path
     *
     * @var \League\Url\Path
     */
    protected $path;

    /**
     * Query
     *
     * @var \League\Url\Query
     */
    protected $query;

    /**
     * Fragment
     *
     * @var \League\Url\Fragment
     */
    protected $fragment;

    /**
     * The constructor
     */
    protected function __construct()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $url = $this->getBaseUrl().$this->getUrl();
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
    public function getUrl(UrlInterface $ref_url = null)
    {
        if (is_null($ref_url)) {
            return $this->path->getUriComponent()
                .$this->query->getUriComponent()
                .$this->fragment->getUriComponent();
        } elseif ($this->getBaseUrl() != $ref_url->getBaseUrl()) {
            return $this->__toString();
        }

        return $this->path->relativeTo($ref_url->getPath())
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
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
     * Return a instance of Url from a string
     *
     * @param string $url a string or an object that implement the __toString method
     *
     * @return static
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromUrl($url)
    {
        $url = (string) $url;
        $url = trim($url);
        $original_url = $url;
        $url = self::sanitizeUrl($url);

        //if no valid scheme is found we add one
        if (is_null($url)) {
            throw new RuntimeException(sprintf('The given URL: `%s` could not be parse', $original_url));
        }
        $components = @parse_url($url);
        if (false === $components) {
            throw new RuntimeException(sprintf('The given URL: `%s` could not be parse', $original_url));
        }

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

        $url = new static;
        $url->scheme = new Scheme($components['scheme']);
        $url->user = new User($components['user']);
        $url->pass = new Pass($components['pass']);
        $url->host = new Host($components['host']);
        $url->port = new Port($components['port']);
        $url->path = new Path($components['path']);
        $url->query = new Query($components['query']);
        $url->fragment = new Fragment($components['fragment']);

        return $url;
    }

    protected static function sanitizeUrl($url)
    {
        if ('' == $url || strpos($url, '//') === 0) {
            return $url;
        } elseif (! preg_match(',^((http|ftp|ws)s?:),i', $url, $matches)) {
            return '//'.$url;
        }

        $scheme_length = strlen($matches[0]);
        if (strpos(substr($url, $scheme_length), '//') === 0) {
            return $url;
        }

        return null;
    }

    /**
     * Return a instance of Url from a server array
     *
     * @param array $server the server array
     *
     * @return static
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
            $header = $server['HTTP_HOST'];
            if (! preg_match('/(:\d+)$/', $header, $matches)) {
                return $header;
            }
            return substr($header, 0, -strlen($matches[1]));
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
}
