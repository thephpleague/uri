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
namespace League\Uri\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Components;

/**
 * a Trait to parse a URI string
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
trait FactoryTrait
{
    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents(static::parse($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return \League\Uri\Interfaces\Schemes\Uri
     */
    public static function createFromComponents(array $components)
    {
        $components = static::formatComponents($components);

        return new static(
            new Components\Scheme($components['scheme']),
            new Components\UserInfo($components['user'], $components['pass']),
            new Components\Host($components['host']),
            new Components\Port($components['port']),
            new Components\HierarchicalPath($components['path']),
            new Components\Query($components['query']),
            new Components\Fragment($components['fragment'])
        );
    }

    /**
     * Format the components to works with all the constructors
     *
     * @param array $components
     *
     * @return array
     */
    protected static function formatComponents(array $components)
    {
        foreach ($components as $name => $value) {
            $components[$name] = (null === $value && 'port' != $name) ? '' : $value;
        }

        return array_merge([
            'scheme' => '', 'user'     => '',
            'pass'   => '', 'host'     => '',
            'port'   => null, 'path'   => '',
            'query'  => '', 'fragment' => '',
        ], $components);
    }

    /**
     * Parse a string as an URI according to the Regexp form rfc3986
     *
     * Parse an URI string and return a hash similar to PHP's parse_url
     *
     * @see http://tools.ietf.org/html/rfc3986#appendix-B
     *
     * @param string $uri The URI to parse
     *
     * @throws InvalidArgumentException if the URI can not be parsed
     *
     * @return array  the array is similar to PHP's parse_url hash response
     */
    protected static function parse($uri)
    {
        $regexp = ',^((?<scheme>[^:/?\#]+):)?(//(?<authority>[^/?\#]*))?
            (?<path>[^?\#]*)(\?(?<query>[^\#]*))?(\#(?<fragment>.*))?,x';
        preg_match($regexp, $uri, $matches);
        $matches = array_merge(['authority' => ''], $matches);
        $components = array_merge(static::parseAuthority($matches['authority']), $matches);

        $default = [
            'scheme' => null, 'user' => null, 'pass' => null, 'host' => null,
            'port' => null, 'path' => null, 'query' => null, 'fragment' => null,
        ];
        $components = array_map(function ($value) {
            return ('' === $value) ? null : $value;
        }, array_merge($default, $components));

        if (null !== $components['port']) {
            $components['port'] = (int) $components['port'];
        }

        return array_intersect_key($components, $default);
    }

    /**
     * Parse a URI authority part into its components
     *
     * @param string $authority
     *
     * @throws \InvalidArgumentException If the authority is not empty with an empty host
     *
     * @return array
     */
    protected static function parseAuthority($authority)
    {
        $components = ['user' => null, 'pass' => null, 'host' => null, 'port' => null];
        if (empty($authority)) {
            return $components;
        }
        preg_match(',^((?<userinfo>.*?)@)?(?<hostname>.*?)?$,', $authority, $matches);
        $matches = array_merge(['userinfo' => '', 'hostname' => ''], $matches);
        $userinfo = explode(':', $matches['userinfo'], 2);
        $components['user'] = array_shift($userinfo);
        $components['pass'] = array_pop($userinfo);
        $components = array_merge($components, static::parseHostname($matches['hostname']));
        if (empty($components['host'])) {
            throw new InvalidArgumentException('The submitted uri is invalid');
        }

        return $components;
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
    protected static function parseHostname($hostname)
    {
        $components = ['host' => '', 'port' => ''];
        $hostname = strrev($hostname);
        if (preg_match("/^((?<port>[^(\[\])]+?):)?(?<host>.*)?$/", $hostname, $res)) {
            $res = array_merge($components, $res);
            $components['host'] = strrev($res['host']);
            $components['port'] = strrev($res['port']);
        }

        return $components;
    }
}
