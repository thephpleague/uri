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

use InvalidArgumentException;
use League\Uri\Interfaces\Uri;
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
    /**
     * Base URI
     *
     * @var Uri|UriInterface
     */
    protected $uri;

    /**
     * New instance
     *
     * @param Uri|UriInterface $uri
     */
    public function __construct($uri)
    {
        if (!uri_reference($uri)['absolute_uri']) {
            throw new InvalidArgumentException('The Base URI must be an Absolute URI');
        }

        $this->uri = $this->hostToAscii($uri);
    }

    /**
     * Convert the Uri host component to its ascii version
     *
     * @param Uri|UriInterface $uri
     *
     * @return Uri|UriInterface
     */
    protected function hostToAscii($uri)
    {
        static $modifier;
        if (null === $modifier) {
            $modifier = new HostToAscii();
        }

        return $modifier($uri);
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
        $payload_normalized = $this->hostToAscii($payload);

        if ($this->uri->getScheme() !== $payload_normalized->getScheme()
            || $this->uri->getAuthority() !== $payload_normalized->getAuthority()
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
