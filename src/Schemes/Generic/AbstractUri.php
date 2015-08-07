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

use League\Uri\Interfaces\Schemes\Uri;
use League\Uri\UriParser;

/**
 * An abstract class to ease URI object creation.
 *
 * @package League.uri
 * @since   4.0.0
 */
abstract class AbstractUri implements Uri
{
    use GenericUriTrait;

    /**
     * Check if a URI is valid
     *
     * @return bool
     */
    abstract protected function isValid();

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
        return static::createFromComponents((new UriParser())->parse($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    abstract public static function createFromComponents(array $components);

    /**
     * Format the components to works with all the constructors
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
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
