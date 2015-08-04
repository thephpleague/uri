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
use League\Uri\Interfaces\Components\Port;

/**
 * a class to parse a URI string according to RFC3986
 *
 * @package League.uri
 * @since   4.0.0
 */
class Parser
{
    /*
     * Ip host validation
     */
    use HostIpTrait;

    /*
     * hostname validation
     */
    use HostnameTrait;

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
    public function parseUri($uri)
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
            return null;
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
            return null;
        }

        $res = filter_var(
            $port,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => Port::MINIMUM, 'max_range' => Port::MAXIMUM]]
        );

        if (!$res) {
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
    protected function isValidLabelsCount(array $labels = [])
    {
        if (127 <= count($labels)) {
            throw new InvalidArgumentException('Invalid Hostname, verify labels count');
        }
    }

    /**
     * Parse a query string into an associative array
     *
     * Multiple identical key will generate an array. This function
     * differ from PHP parse_str as:
     *    - it does not modify or remove parameters keys
     *    - it does not create nested array
     *
     * @param string    $str          The query string to parse
     * @param array     $separator    The query string separator
     * @param int|false $encodingType The query string encoding mechanism
     *
     * @return array
     */
    public function parseQuery($str, $separator = '&', $encodingType = PHP_QUERY_RFC3986)
    {
        if ('' == $str) {
            return [];
        }
        $res     = [];
        $pairs   = explode($separator, $str);
        $decoder = $this->getDecoder($encodingType);
        foreach ($pairs as $pair) {
            $res = $this->parsePair($decoder, $res, $pair);
        }

        return $res;
    }

    /**
     * Parse a query string pair
     *
     * @param callable $decoder a Callable to decode the query string pair
     * @param array    $res     The associative array to add the pair to
     * @param string   $pair    The query string pair
     *
     * @return array
     */
    protected function parsePair(callable $decoder, array $res, $pair)
    {
        $param = explode('=', $pair, 2);
        $key   = $decoder(array_shift($param));
        $value = $decoder(array_shift($param));

        if (!array_key_exists($key, $res)) {
            $res[$key] = $value;

            return $res;
        }
        if (!is_array($res[$key])) {
            $res[$key] = [$res[$key]];
        }
        $res[$key][] = $value;

        return $res;
    }

    /**
     * Build a query string from an associative array
     *
     * The method expects the return value from Query::parse to build
     * a valid query string. This method differs from PHP http_build_query as:
     *
     *    - it does not modify parameters keys
     *
     * @param array     $arr          Query string parameters
     * @param array     $separator    Query string separator
     * @param int|false $encodingType Query string encoding
     *
     * @return string
     */
    public function buildQuery(array $arr, $separator = '&', $encodingType = PHP_QUERY_RFC3986)
    {
        $encoder = $this->getEncoder($encodingType);
        $arr     = array_map(function ($value) {
            return !is_array($value) ? [$value] : $value;
        }, $arr);

        $pairs = [];
        foreach ($arr as $key => $value) {
            $pairs = array_merge($pairs, $this->buildPair($encoder, $encoder($key), $value));
        }

        return implode($separator, $pairs);
    }

    /**
     * Build a query key/pair association
     *
     * @param callable $encoder a Callable to encode the key/pair association
     * @param string   $key     The query string key
     * @param string   $value   The query string value
     *
     * @return string
     */
    protected function buildPair(callable $encoder, $key, array $value = [])
    {
        return array_reduce($value, function (array $carry, $data) use ($key, $encoder) {
            $pair = $key;
            if (null !== $data) {
                $pair .= '='.$encoder($data);
            }
            $carry[] = $pair;

            return $carry;
        }, []);
    }

    /**
     * Return the query string decoding mechanism
     *
     * @param int|false $encodingType
     *
     * @return callable
     */
    protected function getDecoder($encodingType)
    {
        if (PHP_QUERY_RFC3986 == $encodingType) {
            return function ($value) {
                return null !== $value ? rawurldecode($value) : null;
            };
        }
        if (PHP_QUERY_RFC1738 == $encodingType) {
            return function ($value) {
                return null !== $value ? urldecode($value) : null;
            };
        }
        if (false !== $encodingType) {
            throw new InvalidArgumentException('Unknown encodingType');
        }

        return function ($value) {
            return null !== $value ? rawurldecode(str_replace('+', ' ', $value)) : null;
        };
    }

    /**
     * Return the query string encoding mechanism
     *
     * @param int|false $encodingType
     *
     * @return callable
     */
    protected function getEncoder($encodingType)
    {
        if (PHP_QUERY_RFC3986 == $encodingType) {
            return function ($value) {
                return rawurlencode($value);
            };
        }
        if (PHP_QUERY_RFC1738 == $encodingType) {
            return function ($value) {
                return urlencode($value);
            };
        }
        if (false !== $encodingType) {
            throw new InvalidArgumentException('Unknown encodingType');
        }

        return function ($value) {
            return $value;
        };
    }
}
