<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Schemes;

use InvalidArgumentException;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Interfaces\Schemes\Http as HttpUriInterface;
use League\Uri\Interfaces\Schemes\Uri;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;
use League\Uri\UriParser;
use Psr\Http\Message\UriInterface;

/**
 * Value object representing HTTP and HTTPS Uri.
 *
 * @package League.uri
 * @since   4.0.0
 */
class Http extends AbstractHierarchicalUri implements HttpUriInterface, UriInterface
{
    /**
     * {@inheritdoc}
     */
    protected static $supportedSchemes = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        return $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Uri $relative)
    {
        if (!$relative instanceof HttpUriInterface || !empty($relative->getScheme())) {
            return $relative->withoutDotSegments();
        }

        if (!empty($relative->getHost())) {
            return $relative->withScheme($this->getScheme())->withoutDotSegments();
        }

        return $this->resolveRelative($relative)->withFragment($relative->getFragment())->withoutDotSegments();
    }

    /**
     * returns the resolve URI
     *
     * @param HttpUriInterface $relative the relative URI
     *
     * @return static
     */
    protected function resolveRelative(HttpUriInterface $relative)
    {
        $path  = $relative->getPath();
        $query = $relative->getQuery();
        if (!empty($path)) {
            return $this->resolveRelativePath($path, $query);
        }

        if (!empty($query)) {
            return $this->withQuery($query);
        }

        return $this;
    }

    /**
     * Return the resolve URI with a updated path and query
     *
     * @param string $path  The relative path string
     * @param string $query The relative query string
     *
     * @return static
     */
    protected function resolveRelativePath($path, $query)
    {
        $relativePath = $this->path->modify($path);
        if ($relativePath->isAbsolute()) {
            return $this->withPath($relativePath)->withQuery($query);
        }

        $segments = $this->path->toArray();
        array_pop($segments);
        $isAbsolute = HierarchicalPath::IS_RELATIVE;
        if ($this->path->isEmpty() || $this->path->isAbsolute()) {
            $isAbsolute = HierarchicalPath::IS_ABSOLUTE;
        }

        $relativePath = $relativePath->createFromArray(
            array_merge($segments, $relativePath->toArray()),
            $isAbsolute
        );

        return $this->withPath($relativePath)->withQuery($query);
    }

    /**
     * Create a new instance from the environment
     *
     * @param array $server the server and execution environment information array tipycally ($_SERVER)
     *
     * @throws InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromServer(array $server)
    {
        return static::createFromString(
            static::fetchServerScheme($server).'//'
            .static::fetchServerUserInfo($server)
            .static::fetchServerHost($server)
            .static::fetchServerPort($server)
            .static::fetchServerRequestUri($server)
        );
    }

    /**
     * Returns the environment scheme
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerScheme(array $server)
    {
        $args = filter_var_array($server, [
            'HTTPS' => ['filter' => FILTER_SANITIZE_STRING, 'options' => ['default' => '']],
        ]);

        return  (empty($args['HTTPS']) || 'off' == $args['HTTPS'])  ? 'http:' : 'https:';
    }

    /**
     * Returns the environment host
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @throws InvalidArgumentException If the host can not be detected
     *
     * @return string
     */
    protected static function fetchServerHost(array $server)
    {
        if (isset($server['HTTP_HOST'])) {
            return static::fetchServerHostname($server['HTTP_HOST']);
        }

        if (!isset($server['SERVER_ADDR'])) {
            throw new InvalidArgumentException('Host could not be detected');
        }

        if (filter_var($server['SERVER_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return '['.$server['SERVER_ADDR'].']';
        }

        return $server['SERVER_ADDR'];
    }

    /**
     * Returns the environment hostname
     *
     * @param string $host the environment server hostname
     *                     the port info can sometimes be
     *                     associated with the hostname
     *
     * @return string
     */
    protected static function fetchServerHostname($host)
    {
        if (preg_match("/^(.*)(:\d+)$/", $host, $matches)) {
            return $matches[1];
        }

        return $host;
    }

    /**
     * Returns the environment user info
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerUserInfo(array $server)
    {
        $user = (array_key_exists('PHP_AUTH_USER', $server)) ? $server['PHP_AUTH_USER'] : null;
        $pass = (array_key_exists('PHP_AUTH_PW', $server)) ? $server['PHP_AUTH_PW'] : null;

        return (new UriParser())->buildUserInfo($user, $pass);
    }

    /**
     * Returns the environment port
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerPort(array $server)
    {
        if (isset($server['HTTP_HOST']) && preg_match("/^(.*)(:\d+)$/", $server['HTTP_HOST'], $matches)) {
            return $matches[2];
        }

        if (isset($server['SERVER_PORT'])) {
            return ':'.$server['SERVER_PORT'];
        }

        return '';
    }

    /**
     * Returns the environment path
     *
     * @param array $server the environment server typically $_SERVER
     *
     * @return string
     */
    protected static function fetchServerRequestUri(array $server)
    {
        if (isset($server['REQUEST_URI'])) {
            return $server['REQUEST_URI'];
        }

        $request = '';
        if (isset($server['PHP_SELF'])) {
            $request .= $server['PHP_SELF'];
        }

        if (isset($server['QUERY_STRING'])) {
            $request .= '?'.$server['QUERY_STRING'];
        }

        return $request;
    }
}
