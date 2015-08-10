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
namespace League\Uri;

use InvalidArgumentException;
use League\Uri\Components\HostIpTrait;
use League\Uri\Components\HostnameTrait;
use League\Uri\Components\PortValidatorTrait;
use League\Uri\Schemes\Generic\PathFormatterTrait;
use RuntimeException;

/**
 * a class to parse a URI string according to RFC3986
 *
 * @package League.uri
 * @since   4.0.0
 */
class UriParser
{
    use HostIpTrait;

    use HostnameTrait;

    use PortValidatorTrait;

    use PathFormatterTrait;

    /**
     * default components hash table
     *
     * @var array
     */
    protected $components = [
        'scheme' => null, 'user' => null, 'pass' => null, 'host' => null,
        'port' => null, 'path' => null, 'query' => null, 'fragment' => null,
    ];

    /**
     * Format the components to works with all the constructors
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
     *
     * @return array
     */
    public function formatComponents(array $components)
    {
        return array_merge($this->components, $components);
    }

    /**
     * Parse a string as an URI according to the regexp form rfc3986
     *
     * Parse an URI string and return a hash similar to PHP's parse_url
     *
     * @see http://tools.ietf.org/html/rfc3986#appendix-B
     *
     * @param string $uri The URI to parse
     *
     * @throws InvalidArgumentException if the URI can not be parsed
     *
     * @return array the array is similar to PHP's parse_url hash response
     */
    public function parse($uri)
    {
        $uriRegexp = ',^((?<scheme>[^:/?\#]+):)?(?<authority>//(?<acontent>[^/?\#]*))?
            (?<path>[^?\#]*)(?<query>\?(?<qcontent>[^\#]*))?
            (?<fragment>\#(?<fcontent>.*))?,x';
        preg_match($uriRegexp, $uri, $parts);

        $parts = $parts + [
            'scheme' => '', 'authority' => '', 'acontent' => '',
            'path' => '', 'query' => '', 'qcontent' => '',
            'fragment' => '', 'fcontent' => '',
        ];
        $parts['scheme'] = $this->formatScheme($parts['scheme']);
        $parts['query'] = (empty($parts['query'])) ? null : $parts['qcontent'];
        $parts['fragment'] = (empty($parts['fragment'])) ? null : $parts['fcontent'];
        $parts = $parts + $this->parseAuthority($parts);

        return array_replace($this->components, array_intersect_key($parts, $this->components));
    }

    /**
     * Build a URI according to RFC3986 from a hash similar to PHP's parse_url
     * No normalization is done on the build uri
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     *
     * @param array $components The hash part array
     *
     * @throws InvalidArgumentException if some components value are invalid
     * @throws RuntimeException         if the resulting uri is invalid
     *
     * @return string
     */
    public function build(array $components)
    {
        $components = $this->formatComponents($components);
        $scheme = $this->filterScheme($components['scheme']);
        $userinfo = $this->getUserInfo($components['user'], $components['pass']);
        $port = $this->filterPort($components['port']);
        $auth = $this->getAuthority($components['host'], $userinfo, $port);
        $path = $this->filterPath($components['path']);
        $query = $this->filterQuery($components['query']);
        $fragment = (null === $components['fragment']) ? '' : '#'.$components['fragment'];

        if (!$this->isValidUri($scheme, $auth, $path)) {
            throw new RuntimeException('The submitted components will produce an invalid URI');
        }

        return $scheme.$auth.$this->formatPath($path, !empty($auth)).$query.$fragment;
    }

    /**
     * Tell whether the build URI is valid
     *
     * @param string $scheme URI scheme component
     * @param string $auth   URI auth part
     * @param string $path   URI path component
     *
     * @return bool
     */
    public function isValidUri($scheme, $auth, $path)
    {
        if (false === strpos($path, ':')) {
            return true;
        }
        $path = explode(':', $path);
        $path = array_shift($path);

        return !(empty($scheme.$auth) && strpos($path, '/') === false);
    }

    /**
     * Filter and format the scheme for URI string representation
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the scheme is invalid
     *
     * @return string
     */
    protected function filterScheme($scheme)
    {
        $scheme = (string) $this->formatScheme($scheme);
        if (!empty($scheme)) {
            $scheme .= ':';
        }

        return $scheme;
    }

    /**
     * Filter and format the path for URI string representation
     *
     * @param string $path
     *
     * @throws InvalidArgumentException If the path is invalid
     *
     * @return string
     */
    protected function filterPath($path)
    {
        if (preg_match(',[?#],', $path)) {
            throw new InvalidArgumentException('the path must not contain a query string or a URI fragment');
        }

        return $path;
    }

    /**
     * Filter and format the query for URI string representation
     *
     * @param string $query
     *
     * @throws InvalidArgumentException If the query is invalid
     *
     * @return string
     */
    protected function filterQuery($query)
    {
        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException('the query string must not contain a URI fragment');
        }

        if (null !== $query) {
            $query = '?'.$query;
        }

        return $query;
    }

    /**
     * Format the user info
     *
     * @return string
     */
    public function getUserInfo($user, $pass)
    {
        $user = $this->filterUser($user);
        $pass = $this->filterPass($pass);
        if (null === $user) {
            return '';
        }

        $userinfo = $user;
        if (null !== $pass) {
            $userinfo .= ':'.$pass;
        }

        return $userinfo.'@';
    }

    /**
     * Filter and format the user for URI string representation
     *
     * @param null|string $user
     *
     * @throws InvalidArgumentException If the user is invalid
     *
     * @return null|string
     */
    protected function filterUser($user)
    {
        if (null === $user || !preg_match(',[/:@?#],', $user)) {
            return $user;
        }

        throw new InvalidArgumentException('The user component contains invalid characters');
    }

    /**
     * Filter and format the pass for URI string representation
     *
     * @param null|string $pass
     *
     * @throws InvalidArgumentException If the pass is invalid
     *
     * @return null|string
     */
    protected function filterPass($pass)
    {
        if (null === $pass || !preg_match(',[/?#@],', $pass)) {
            return $pass;
        }

        throw new InvalidArgumentException('The user component contains invalid characters');
    }

    /**
     * Format a URI authority according to the Formatter properties
     *
     * @param string $host     the URI Host component
     * @param string $userinfo the URI userinfo part
     * @param string $port     the URI Port component
     *
     * @throws InvalidArgumentException If the host is invalid
     *
     * @return string
     */
    public function getAuthority($host, $userinfo, $port)
    {
        if (null === $host) {
            return '';
        }

        if ('' === $host) {
            return '//';
        }

        $this->validateHost($host);

        return '//'.$userinfo.$host.$port;
    }

    /**
     * Filter and format the port for URI string representation
     *
     * @param string $port
     *
     * @throws InvalidArgumentException If the port is invalid
     *
     * @return string
     */
    protected function filterPort($port)
    {
        if (null === $port) {
            return '';
        }
        $port = $this->validatePort($port);
        if (isset($port)) {
            $port = ':'.$port;
        }

        return $port;
    }

    /**
     * validate the scheme component
     *
     * @param null|string $scheme
     *
     * @throws InvalidArgumentException If the scheme is invalid
     *
     * @return null|string
     */
    protected function formatScheme($scheme)
    {
        if (empty($scheme)) {
            return null;
        }

        if (preg_match(',^[a-z]([-a-z0-9+.]+)?$,i', $scheme)) {
            return $scheme;
        }

        throw new InvalidArgumentException(sprintf('The submitted scheme is invalid: `%s`', $scheme));
    }

    /**
     * Parse a URI authority part into its components
     *
     * @param string[] $parts
     *
     * @throws InvalidArgumentException If the authority is not empty with an empty host
     *
     * @return array
     */
    protected function parseAuthority($parts)
    {
        $res = ['user' => null, 'pass' => null, 'host' => null, 'port' => null];
        if (empty($parts['authority'])) {
            return $res;
        }

        if (empty($parts['acontent'])) {
            return array_merge($res, ['host' => '']);
        }

        preg_match(',^(?<userinfo>(?<ucontent>.*?)@)?(?<hostname>.*?)?$,', $parts['acontent'], $parts);
        $parts = $parts + ['userinfo' => null, 'ucontent' => null, 'hostname' => null];

        return $this->parseUserInfo($parts) + $this->parseHostname($parts['hostname']);
    }


    /**
     * Parse the hostname into its components Host and Port
     *
     * No validation is done on the port or host component found
     *
     * @param string $hostname
     *
     * @return array
     */
    protected function parseHostname($hostname)
    {
        $components = ['host' => null, 'port' => null];
        $hostname = strrev($hostname);
        if (preg_match(",^((?<port>[^(\[\])]+?):)?(?<host>.*)?$,", $hostname, $res)) {
            $res = $res + $components;
            $components['host'] = strrev($res['host']);
            $components['port'] = strrev($res['port']);
        }
        $this->validateHost($components['host']);
        $components['port'] = $this->formatPort($components['port']);

        return $components;
    }

    /**
     * validate the host component
     *
     * @param string $host
     *
     * @throws InvalidArgumentException If the host is invalid
     */
    protected function validateHost($host)
    {
        if (empty($this->validateIpHost($host))) {
            $this->validateStringHost($host);
        }
    }

    /**
     * Parse and validate the port component
     *
     * @param null|string $port
     *
     * @throws InvalidArgumentException If the port is invalid
     *
     * @return int|null
     */
    protected function formatPort($port)
    {
        if (empty($port)) {
            return null;
        }

        return $this->validatePort($port);
    }

    /**
     * Parse a URI user information part into its components
     *
     * @param string[] $parts
     *
     * @return array
     */
    protected function parseUserInfo($parts)
    {
        if (empty($parts['userinfo'])) {
            return ['user' => null, 'pass' => null];
        }

        $res = explode(':', $parts['ucontent'], 2);

        return [
            'user' => array_shift($res),
            'pass' => array_pop($res),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setIsAbsolute($host)
    {
        return ('.' == mb_substr($host, -1, 1, 'UTF-8')) ? mb_substr($host, 0, -1, 'UTF-8') : $host;
    }

    /**
     * {@inheritdoc}
     */
    protected function isValidLabelsCount(array $labels)
    {
        if (127 <= count($labels)) {
            throw new InvalidArgumentException('Invalid Hostname, verify labels count');
        }
    }
}
