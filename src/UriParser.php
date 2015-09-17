<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri;

use InvalidArgumentException;
use League\Uri\Components\HostIpTrait;
use League\Uri\Components\HostnameTrait;
use League\Uri\Components\PortValidatorTrait;
use League\Uri\Schemes\Generic\PathFormatterTrait;

/**
 * a class to parse a URI string according to RFC3986
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class UriParser
{
    use HostIpTrait;

    use HostnameTrait;

    use PortValidatorTrait;

    use PathFormatterTrait;

    const REGEXP_URI = ',^((?<scheme>[^:/?\#]+):)?
        (?<authority>//(?<acontent>[^/?\#]*))?
        (?<path>[^?\#]*)
        (?<query>\?(?<qcontent>[^\#]*))?
        (?<fragment>\#(?<fcontent>.*))?,x';

    const REGEXP_AUTHORITY = ',^(?<userinfo>(?<ucontent>.*?)@)?(?<hostname>.*?)?$,';

    const REGEXP_REVERSE_HOSTNAME = ',^((?<port>[^(\[\])]*):)?(?<host>.*)?$,';

    const REGEXP_SCHEME = ',^([a-z]([-a-z0-9+.]+)?)?$,i';

    const REGEXP_INVALID_USER = ',[/?#@:],';

    const REGEXP_INVALID_PASS = ',[/?#@],';

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
     * Normalize URI components hash
     *
     * @param array $components a hash representation of the URI components
     *                          similar to PHP parse_url function result
     *
     * @return array
     */
    public function normalizeUriHash(array $components)
    {
        return array_replace($this->components, $components);
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
     * @return array the array is similar to PHP's parse_url hash response
     */
    public function parse($uri)
    {
        preg_match(self::REGEXP_URI, $uri, $parts);
        $parts += ['query' => '', 'qcontent' => '', 'fragment' => '', 'fcontent' => ''];

        return array_replace(
            $this->components,
            $this->parseAuthority($parts),
            $this->parseSchemeAndPath($parts),
            ['query' => empty($parts['query']) ? null : $parts['qcontent']],
            ['fragment' => empty($parts['fragment']) ? null : $parts['fcontent']]
        );
    }

    /**
     * Parse a URI authority part into its components
     *
     * @param string[] $parts
     *
     * @return array
     */
    protected function parseAuthority(array $parts)
    {
        $res = ['user' => null, 'pass' => null, 'host' => null, 'port' => null];
        if (empty($parts['authority'])) {
            return $res;
        }

        if (empty($parts['acontent'])) {
            return ['host' => ''] + $res;
        }

        preg_match(self::REGEXP_AUTHORITY, $parts['acontent'], $auth);
        if (!empty($auth['userinfo'])) {
            $userinfo = explode(':', $auth['ucontent'], 2);
            $res = ['user' => array_shift($userinfo), 'pass' => array_shift($userinfo)] + $res;
        }

        return $this->parseHostname($auth['hostname']) + $res;
    }

    /**
     * Parse URI scheme and part
     *
     * @param string[] $parts
     *
     * @throws InvalidArgumentException If the scheme is invalid
     *
     * @return array
     */
    protected function parseSchemeAndPath(array $parts)
    {
        $res = ['scheme' => null, 'path' => $parts['path']];
        try {
            $scheme = $this->filterScheme($parts['scheme']);
            $scheme = empty($scheme) ? null : $scheme;

            return ['scheme' => $scheme] + $res;
        } catch (InvalidArgumentException $e) {
            if (empty($parts['authority'])) {
                return ['path' => $parts['scheme'].':'.$parts['path']] + $res;
            }

            throw $e;
        }
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
        if (preg_match(self::REGEXP_REVERSE_HOSTNAME, $hostname, $res)) {
            $components['host'] = strrev($res['host']);
            $components['port'] = strrev($res['port']);
        }
        $components['host'] = $this->filterHost($components['host']);
        $components['port'] = $this->validatePort($components['port']);

        return $components;
    }

    /**
     * validate the host component
     *
     * @param string $host
     *
     * @return int|null
     */
    protected function filterHost($host)
    {
        if (empty($this->validateIpHost($host))) {
            $this->validateStringHost($host);
        }

        return $host;
    }

    /**
     * @inheritdoc
     */
    protected function setIsAbsolute($host)
    {
        return ('.' == mb_substr($host, -1, 1, 'UTF-8')) ? mb_substr($host, 0, -1, 'UTF-8') : $host;
    }

    /**
     * @inheritdoc
     */
    protected function assertLabelsCount(array $labels)
    {
        if (127 <= count($labels)) {
            throw new InvalidArgumentException('Invalid Host, verify labels count');
        }
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
    protected function filterScheme($scheme)
    {
        if (preg_match(self::REGEXP_SCHEME, $scheme)) {
            return $scheme;
        }

        throw new InvalidArgumentException(sprintf('The submitted scheme is invalid: `%s`', $scheme));
    }

    /**
     * Format the user info
     *
     * @param string $user
     * @param string $pass
     *
     * @return string
     */
    public function buildUserInfo($user, $pass)
    {
        $userinfo = $this->filterUser($user);
        if (null === $userinfo) {
            return '';
        }

        $pass = $this->filterPass($pass);
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
        if (!preg_match(self::REGEXP_INVALID_USER, $user)) {
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
        if (!preg_match(self::REGEXP_INVALID_PASS, $pass)) {
            return $pass;
        }

        throw new InvalidArgumentException('The user component contains invalid characters');
    }
}
