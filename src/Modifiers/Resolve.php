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
        $this->assertUriObject($uri);
        $this->uri = $uri;
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
        $meta = uri_reference($payload);
        $path = $payload->getPath();
        if ($meta['absolute_uri']) {
            return $payload
                ->withPath((new Path($path))->withoutDotSegments()->__toString());
        }

        if ($meta['network_path']) {
            return $payload
                ->withScheme($this->uri->getScheme())
                ->withPath((new Path($path))->withoutDotSegments()->__toString());
        }

        $userInfo = explode(':', $this->uri->getUserInfo(), 2);
        $components = $this->resolvePathAndQuery($path, $payload->getQuery());

        return $payload
            ->withPath($this->formatPath($components['path']))
            ->withQuery($components['query'])
            ->withHost($this->uri->getHost())
            ->withPort($this->uri->getPort())
            ->withUserInfo((string) array_shift($userInfo), array_shift($userInfo))
            ->withScheme($this->uri->getScheme());
    }

    /**
     * Resolve the URI for a Authority-less payload URI
     *
     * @param string $path  the payload path component
     * @param string $query the payload query component
     *
     * @return string[]
     */
    protected function resolvePathAndQuery($path, $query)
    {
        $components = ['path' => $path, 'query' => $query];

        if ('' === $components['path']) {
            $components['path'] = $this->uri->getPath();
            if ('' === $components['query']) {
                $components['query'] = $this->uri->getQuery();
            }

            return $components;
        }

        if (0 !== strpos($components['path'], '/')) {
            $components['path'] = $this->mergePath($components['path']);
        }

        return $components;
    }

    /**
     * Merging Relative URI path with Base URI path
     *
     * @param string $path
     *
     * @return string
     */
    protected function mergePath($path)
    {
        $basePath = $this->uri->getPath();
        if ('' !== $this->uri->getAuthority() && '' === $basePath) {
            return (string) (new Path($path))->withLeadingSlash();
        }

        if ('' !== $basePath) {
            $segments = explode('/', $basePath);
            array_pop($segments);
            $path = implode('/', $segments).'/'.$path;
        }

        return $path;
    }

    /**
     * Format the resolved path
     *
     * @param string $path
     *
     * @return string
     */
    protected function formatPath($path)
    {
        $path = (new Path($path))->withoutDotSegments();
        if ('' !== $this->uri->getAuthority() && '' !== $path->__toString()) {
            $path = $path->withLeadingSlash();
        }

        return (string) $path;
    }
}
