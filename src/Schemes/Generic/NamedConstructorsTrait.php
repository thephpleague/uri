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

use League\Uri;
use ReflectionClass;

/**
 * a Trait to add more convenient Named constructors
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
trait NamedConstructorsTrait
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
     * Create a new instance from an array returned by
     * PHP parse_url function
     *
     * @param array $components
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return Uri\Interfaces\Schemes\Uri
     */
    public static function createFromComponents(array $components)
    {
        $components = static::formatComponents($components);

        return (new ReflectionClass(get_called_class()))->newInstance(
            new Uri\Components\Scheme($components['scheme']),
            new Uri\Components\UserInfo($components['user'], $components['pass']),
            new Uri\Components\Host($components['host']),
            new Uri\Components\Port($components['port']),
            new Uri\Components\Path($components['path']),
            new Uri\Components\Query($components['query']),
            new Uri\Components\Fragment($components['fragment'])
        );
    }
}
