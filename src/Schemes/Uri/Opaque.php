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
namespace League\Uri\Schemes\Uri;

use InvalidArgumentException;
use League\Uri\Interfaces;
use Psr\Http\Message\UriInterface;

/**
 * a Trait to access URI properties methods
 *
 * @package League.uri
 * @since   1.0.0
 *
 */
trait Opaque
{
    /*
     * URI complementary methods
     */
    use Properties;

    /**
     * {@inheritdoc}
     */
    public function sameValueAs($uri)
    {
        if (!$uri instanceof UriInterface && !$uri instanceof Interfaces\Schemes\Uri) {
            throw new InvalidArgumentException(
                'You must provide an object implementing the `Psr\Http\Message\UriInterface` or
                the `League\Uri\Interfaces\Schemes\Uri` interface'
            );
        }

        return $uri->__toString() === $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function isOpaque()
    {
        return true;
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components
     *
     * @return static
     */
    public static function createFromComponents(array $components)
    {
        $components = static::formatComponents($components);

        return static::createFromString($components['scheme'] . ':' . $components['path']);
    }
}
