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

use League\Uri\Components\HierarchicalPath;
use League\Uri\Interfaces\Uri as LeagueUriInterface;
use League\Uri\Modifiers\Filters\Uri;
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
    use Uri;

    /**
     * Generate the base URI to be used
     *
     * @param LeagueUriInterface|UriInterface $relative
     *
     * @return LeagueUriInterface|UriInterface
     */
    protected function getBaseUri($relative)
    {
        $userinfo = explode(':', $this->uri->getUserInfo(), 2);
        $user = array_shift($userinfo);
        $pass = array_shift($userinfo);

        return $relative
            ->withHost($this->uri->getHost())
            ->withScheme($this->uri->getScheme())
            ->withUserInfo($user, $pass)
            ->withPort($this->uri->getPort());
    }

    /**
     * New instance
     *
     * @param LeagueUriInterface|UriInterface $uri
     */
    public function __construct($uri)
    {
        $this->uri = $this->filterUri($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($relative)
    {
        $this->assertUriObject($relative);
        $uri = $this->generate($relative);

        return (new RemoveDotSegments())->__invoke($uri);
    }

    /**
     * @param LeagueUriInterface|UriInterface $relative
     *
     * @return LeagueUriInterface|UriInterface
     */
    protected function generate($relative)
    {
        $scheme = $relative->getScheme();
        if (!empty($scheme) && $scheme != $this->uri->getScheme()) {
            return $relative;
        }

        if (!empty($relative->getAuthority())) {
            return $relative->withScheme($this->uri->getScheme());
        }

        return $this->resolveRelative($relative)->withFragment($relative->getFragment());
    }

    /**
     * returns the resolve URI
     *
     * @param LeagueUriInterface|UriInterface $relative the relative URI
     *
     * @return LeagueUriInterface|UriInterface
     */
    protected function resolveRelative($relative)
    {
        $path  = $relative->getPath();
        if (!empty($path)) {
            return $this->resolveRelativePath($relative, $path, $relative->getQuery());
        }

        $query = $relative->getQuery();
        if (!empty($query)) {
            return $this->getBaseUri($relative)
                ->withPath($this->uri->getPath())
                ->withQuery($query);
        }

        return $this->getBaseUri($relative)
            ->withPath($this->uri->getPath())
            ->withQuery($this->uri->getQuery());
    }

    /**
     * Return the resolve URI with a updated path and query
     *
     * @param LeagueUriInterface|UriInterface $relative the relative URI
     * @param string                          $path     The relative path string
     * @param string                          $query    The relative query string
     *
     * @return LeagueUriInterface|UriInterface
     */
    protected function resolveRelativePath($relative, $path, $query)
    {
        $relativePath = new HierarchicalPath($path);
        if ($relativePath->isAbsolute()) {
            return $this->getBaseUri($relative)
                ->withPath($relativePath)
                ->withQuery($query);
        }

        $originalUri = $relativePath->modify($this->uri->getPath());
        $segments = $originalUri->toArray();
        array_pop($segments);
        $isAbsolute = HierarchicalPath::IS_RELATIVE;
        if (empty($originalUri) || $originalUri->isAbsolute()) {
            $isAbsolute = HierarchicalPath::IS_ABSOLUTE;
        }

        $relativePath = $relativePath->createFromArray(
            array_merge($segments, $relativePath->toArray()),
            $isAbsolute
        );

        return $this->getBaseUri($relative)
            ->withPath($relativePath->__toString())
            ->withQuery($query);
    }
}
