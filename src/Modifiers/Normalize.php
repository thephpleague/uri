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
namespace League\Uri\Modifiers;

use League\Uri\Interfaces\Uri;

/**
 * A class to normalize URI objects
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Normalize extends AbstractUriModifier
{
    private static $modifier;

    /**
     * Initialize and access a Pipeline object which will
     *
     *  - convert host to their ascii representation
     *  - removing path dot segments according to RFC3986
     *  - sorted query string according to their key values
     *
     * @return Pipeline
     */
    private static function getModifier()
    {
        if (!static::$modifier instanceof Pipeline) {
            static::$modifier = new Pipeline([
                new HostToAscii(),
                new RemoveDotSegments(),
                new KsortQuery(),
            ]);
        }

        return static::$modifier;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($uri)
    {
        $this->assertUriObject($uri);

        return self::getModifier()
            ->process($uri)
            ->withScheme(mb_strtolower($uri->getScheme()));
    }
}
