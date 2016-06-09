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
use League\Uri\Modifiers\Filters\Uri as UriFilter;
use Psr\Http\Message\UriInterface;

/**
 * Resolve an URI according to a base URI using
 * RFC3986 rules
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
class Relativize extends AbstractUriModifier
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

        return $this->relativizeUri($payload);
    }

    /**
     * Resolve the payload URI
     *
     * @param Uri|UriInterface $payload
     *
     * @return Uri|UriInterface
     */
    protected function relativizeUri($payload)
    {
        if ($this->uri->getScheme() !== $payload->getScheme()
            || $this->uri->getAuthority() !== $payload->getAuthority()
        ) {
            return $payload;
        }

        $path = $this->relativizePath($payload->getPath());

        return $this->uri
            ->withScheme('')
            ->withPort(null)
            ->withUserInfo('')
            ->withHost('')
            ->withPath($path)
            ->withQuery($payload->getQuery())
            ->withFragment($payload->getFragment());
    }

    /**
     * Relative the URI for a authority-less payload URI
     *
     * @param string $path
     *
     * @return string
     */
    protected function relativizePath($path)
    {
        $targetSegments = $this->getSegments($path);
        $basename = array_pop($targetSegments);
        $basePath = $this->uri->getPath();
        if ($basePath === $path) {
            return $this->formatPath($basename, $basePath);
        }

        $baseSegments = $this->getSegments($basePath);
        array_pop($baseSegments);
        foreach ($baseSegments as $offset => $segment) {
            if (!isset($targetSegments[$offset]) || $segment !== $targetSegments[$offset]) {
                break;
            }
            unset($baseSegments[$offset], $targetSegments[$offset]);
        }
        $targetSegments[] = $basename;
        $path = str_repeat('../', count($baseSegments)).implode('/', $targetSegments);

        return $this->formatPath($path, $basePath);
    }

    /**
     * returns the path segments
     *
     * @param string $path
     *
     * @return array
     */
    protected function getSegments($path)
    {
        if ('' !== $path && '/' === $path[0]) {
            $path = mb_substr($path, 1);
        }

        return explode('/', $path);
    }

    /**
     * Post formatting the path to keep a valid URI
     *
     * @param string $path
     * @param string $basePath
     *
     * @return string
     */
    protected function formatPath($path, $basePath)
    {
        if ('' === $path) {
            return in_array($basePath, ['', '/']) ? '/' : './';
        }

        if (false === ($colonPos = strpos($path, ':'))) {
            return $path;
        }

        $slashPos = strpos($path, '/');
        if (false === $slashPos || $colonPos < $slashPos) {
            return "./$path";
        }

        return $path;
    }
}
