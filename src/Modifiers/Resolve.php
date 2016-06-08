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

use League\Uri\Components\Path;
use League\Uri\Interfaces\Uri;
use League\Uri\Modifiers\Filters\Uri as UriFilter;
use Psr\Http\Message\UriInterface;

/**
 * Resolve an URI according to a base URI using
 * RFC3986 rules
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Resolve extends AbstractUriModifier
{
    use UriFilter;

    /**
     * New instance
     *
     * @param Uri|UriInterface $uri
     */
    public function __construct($uri)
    {
        $this->uri = $this->filterUri($uri);
    }

    /**
     * Return a Uri object modified according to the modifier
     *
     * @param Uri|UriInterface $payload
     *
     * @return Uri|UriInterface
     */
    public function __invoke($payload)
    {
        $this->assertUriObject($payload);
        $uri = $this->resolveUri($payload);

        $path = (new Path($uri->getPath()))->withoutDotSegments();
        if ('' !== $uri->getAuthority() && '' !== $path->__toString()) {
            $path = $path->withLeadingSlash();
        }

        return $uri->withPath((string) $path);
    }

    /**
     * Resolve the payload URI
     *
     * @param Uri|UriInterface $payload
     *
     * @return Uri|UriInterface
     */
    protected function resolveUri($payload)
    {
        if ('' === (string) $payload) {
            return $this->uri;
        }

        if ('' !== $payload->getScheme()) {
            return $payload;
        }

        if ('' !== $payload->getAuthority()) {
            return $payload->withScheme($this->uri->getScheme());
        }

        return $this
            ->resolveDomain($payload->getPath(), $payload->getQuery())
            ->withFragment($payload->getFragment());
    }

    /**
     * Resolve the URI for a Authority-less payload URI
     *
     * @param string $path
     * @param string $query
     *
     * @return Uri|UriInterface
     */
    protected function resolveDomain($path, $query)
    {
        if ('' === $path) {
            if ('' === $query) {
                $query = $this->uri->getQuery();
            }

            return $this->uri->withQuery($query);
        }

        if (0 === strpos($path, '/')) {
            return $this->uri->withPath($path)->withQuery($query);
        }

        return $this->uri
            ->withPath($this->mergePath($path)->__toString())
            ->withQuery($query);
    }

    /**
     * Merging Relative URI path with Base URI path
     *
     * @param string $path
     *
     * @return PathInterface
     */
    protected function mergePath($path)
    {
        $basePath = $this->uri->getPath();
        if ('' !== $this->uri->getAuthority() && '' === $basePath) {
            return (new Path($path))->withLeadingSlash();
        }

        if ('' !== $basePath) {
            $segments = explode('/', $basePath);
            array_pop($segments);
            $path = implode('/', $segments).'/'.$path;
        }

        return new Path($path);
    }
}
