<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.2.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Modifiers;

use League\Uri\Interfaces\Uri;
use League\Uri\Types\TranscoderTrait;
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
    use TranscoderTrait;

    /**
     * Return a Uri object modified according to the modifier
     *
     * @param Uri|UriInterface $uri
     *
     * @return Uri|UriInterface
     */
    public function __invoke($uri)
    {
        $modifier = new Pipeline([new HostToAscii(), new KsortQuery()]);

        $path = $uri->getPath();
        if ('' !== $uri->getScheme().$uri->getAuthority() || (isset($path[0]) && '/' === $path[0])) {
            $modifier = $modifier->pipe(new RemoveDotSegments());
        }

        return $this->decodeUri($modifier($uri));
    }

    /**
     * Decode specific component of the URI
     *
     * @param Uri|UriInterface $uri
     *
     * @return Uri|UriInterface
     */
    protected function decodeUri($uri)
    {
        foreach (['Path', 'Query', 'Fragment'] as $part) {
            $uri = $this->decodeUriPart($uri, $part);
        }

        return $uri;
    }

    /**
     * Decode an URI part
     *
     * @param Uri|UriInterface $uri
     * @param string           $property
     *
     * @return Uri|UriInterface
     */
    protected function decodeUriPart($uri, $property)
    {
        $value = preg_replace_callback(
            ',%('.self::$unreservedCharsEncoded.'),i',
            function (array $matches) {
                return rawurldecode($matches[0]);
            },
            call_user_func([$uri, 'get'.$property])
        );

        return call_user_func([$uri, 'with'.$property], $value);
    }
}
