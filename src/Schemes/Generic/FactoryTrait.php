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

use League\Uri\Components;
use League\Uri\Parser;

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
     * URI Parser
     *
     * @var Parser
     */
    protected static $uriParser;

    /**
     * Return a UriParser object
     *
     * @return Parser
     */
    protected static function getUriParser()
    {
        if (!static::$uriParser instanceof Parser) {
            static::$uriParser = new Parser();
        }

        return static::$uriParser;
    }

    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return FactoryTrait
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents(static::getUriParser()->parse($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return FactoryTrait
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
        return array_merge([
            'scheme' => null, 'user' => null, 'pass' => null, 'host' => null,
            'port' => null, 'path' => '', 'query' => null, 'fragment' => null,
        ], $components);
    }
}
