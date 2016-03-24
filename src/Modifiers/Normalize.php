<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.1
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Modifiers;

use League\Uri\Interfaces\Uri;
use Psr\Http\Message\UriInterface;

/**
 * A class to normalize URI objects
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Normalize extends AbstractUriModifier
{
    /**
     * Return a Uri object modified according to the modifier
     *
     * @param Uri|UriInterface $uri
     *
     * @return Uri|UriInterface
     */
    public function __invoke($uri)
    {
        $this->assertUriObject($uri);

        static $modifier;
        if (!$modifier instanceof Pipeline) {
            $modifier = new Pipeline([
                new HostToAscii(),
                new RemoveDotSegments(),
                new KsortQuery(),
            ]);
        }

        return $modifier
            ->process($uri)
            ->withScheme(mb_strtolower($uri->getScheme(), 'UTF-8'));
    }
}
