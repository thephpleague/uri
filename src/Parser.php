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

/**
 * a class to parse a URI string according to RFC3986
 *
 * @package League.uri
 * @since   4.0.0
 */
class Parser
{
    /**
     * RFC3986 URI Regexp expression
     *
     * @see http://tools.ietf.org/html/rfc3986#appendix-B
     */
    const URI_REGEXP = ',^((?<scheme>[^:/?\#]+):)?
        (?<authority>//(?<acontent>[^/?\#]*))?
        (?<path>[^?\#]*)
        (?<query>\?(?<qcontent>[^\#]*))?
        (?<fragment>\#(?<fcontent>.*))?,x';

    /**
     * Authoriy URI Regular Expression
     */
    const AUTHORITY_REGEXP = ',^(?<userinfo>(?<ucontent>.*?)@)?(?<hostname>.*?)?$,';

    /**
     * Reverse Host + port URI Regular Expression
     */
    const REVERSE_HOSTNAME_REGEXP = ",^((?<port>[^(\[\])]+?):)?(?<host>.*)?$,";

    /**
     * default components hash table
     *
     * @var array
     */
    protected $components = [
        'scheme' => null, 'user' => null, 'pass' => null, 'host' => null,
        'port' => null, 'path' => null, 'query' => null, 'fragment' => null,
    ];

    /*
     * Ip host validation
     */
    use Components\HostIpTrait;

    /*
     * hostname validation
     */
    use Components\HostnameTrait;

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
        preg_match(self::URI_REGEXP, $uri, $parts);
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
            return;
        }

        if (preg_match('/^[a-z][-a-z0-9+.]+$/i', $scheme)) {
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
        if (empty($parts['authority'])) {
            return ['user' => null, 'pass' => null, 'host' => null, 'port' => null];
        }

        if (empty($parts['acontent'])) {
            return ['user' => null, 'pass' => null, 'host' => '', 'port' => null];
        }

        preg_match(self::AUTHORITY_REGEXP, $parts['acontent'], $parts);
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
        if (preg_match(self::REVERSE_HOSTNAME_REGEXP, $hostname, $res)) {
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
            return;
        }

        if (!($res = filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]))) {
            throw new InvalidArgumentException(sprintf('The submitted port is invalid: `%s`', $port));
        }

        return $res;
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
