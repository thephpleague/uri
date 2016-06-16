<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2016 Ignace Nyamagana Butera
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
        if ($this->uri->getScheme() !== $payload->getScheme()
            || $this->uri->getAuthority() !== $payload->getAuthority()
        ) {
            return $payload;
        }

        $path = $this->relativizePath($payload->getPath());

        return $payload
            ->withScheme('')
            ->withPort(null)
            ->withUserInfo('')
            ->withHost('')
            ->withPath($this->formatPath($path));
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
        $segments = $this->getSegments($path);
        $basename = array_pop($segments);
        $basePath = $this->uri->getPath();
        if ($basePath === $path) {
            return $basename;
        }

        $baseSegments = $this->getSegments($basePath);
        array_pop($baseSegments);
        foreach ($baseSegments as $offset => $segment) {
            if (!isset($segments[$offset]) || $segment !== $segments[$offset]) {
                break;
            }
            unset($baseSegments[$offset], $segments[$offset]);
        }
        $segments[] = $basename;

        return str_repeat('../', count($baseSegments)).implode('/', $segments);
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
            $path = substr($path, 1);
        }

        return explode('/', $path);
    }

    /**
     * Post formatting the path to keep a valid URI
     *
     * @param string $path
     *
     * @return string
     */
    protected function formatPath($path)
    {
        if ('' === $path) {
            $basePath = $this->uri->getPath();

            return in_array($basePath, ['', '/']) ? $basePath : './';
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
