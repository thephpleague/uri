<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Uri;

use League\Uri\Contracts\UriInterface;
use Psr\Http\Message\UriInterface as Psr7UriInterface;
use Stringable;
use function array_pop;
use function array_reduce;
use function count;
use function end;
use function explode;
use function implode;
use function in_array;
use function str_repeat;
use function strpos;
use function substr;

final class UriResolver
{
    /**
     * @var array<string,int>
     */
    const DOT_SEGMENTS = ['.' => 1, '..' => 1];

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Input URI normalization to allow Stringable and string URI.
     */
    private static function filterUri(Psr7UriInterface|UriInterface|Stringable|string $uri): Psr7UriInterface|UriInterface
    {
        return match (true) {
            $uri instanceof Psr7UriInterface, $uri instanceof UriInterface => $uri,
            default => Uri::new($uri),
        };
    }

    /**
     * Resolves a URI against a base URI using RFC3986 rules.
     *
     * If the first argument is a UriInterface the method returns a UriInterface object
     * If the first argument is a Psr7UriInterface the method returns a Psr7UriInterface object
     */
    public static function resolve(
        Psr7UriInterface|UriInterface|Stringable|string $uri,
        Psr7UriInterface|UriInterface|Stringable|string $baseUri
    ): Psr7UriInterface|UriInterface {
        $uri = self::filterUri($uri);
        $baseUri = self::filterUri($baseUri);

        $null = $uri instanceof Psr7UriInterface ? '' : null;

        if ($null !== $uri->getScheme()) {
            return $uri
                ->withPath(self::removeDotSegments($uri->getPath()));
        }

        if ($null !== $uri->getAuthority()) {
            return $uri
                ->withScheme($baseUri->getScheme())
                ->withPath(self::removeDotSegments($uri->getPath()));
        }

        $user = $null;
        $pass = null;
        $userInfo = $baseUri->getUserInfo();
        if (null !== $userInfo) {
            [$user, $pass] = explode(':', $userInfo, 2) + [1 => null];
        }

        [$uri_path, $uri_query] = self::resolvePathAndQuery($uri, $baseUri);

        return $uri
            ->withPath(self::removeDotSegments($uri_path))
            ->withQuery($uri_query)
            ->withHost($baseUri->getHost())
            ->withPort($baseUri->getPort())
            ->withUserInfo((string) $user, $pass)
            ->withScheme($baseUri->getScheme())
        ;
    }

    /**
     * Remove dot segments from the URI path.
     */
    private static function removeDotSegments(string $path): string
    {
        if (!str_contains($path, '.')) {
            return $path;
        }

        $old_segments = explode('/', $path);
        $new_path = implode('/', array_reduce($old_segments, UriResolver::reducer(...), []));
        if (isset(self::DOT_SEGMENTS[end($old_segments)])) {
            $new_path .= '/';
        }

        // @codeCoverageIgnoreStart
        // added because some PSR-7 implementations do not respect RFC3986
        if (str_starts_with($path, '/') && !str_starts_with($new_path, '/')) {
            return '/'.$new_path;
        }
        // @codeCoverageIgnoreEnd

        return $new_path;
    }

    /**
     * Remove dot segments.
     *
     * @return array<int, string>
     */
    private static function reducer(array $carry, string $segment): array
    {
        if ('..' === $segment) {
            array_pop($carry);

            return $carry;
        }

        if (!isset(self::DOT_SEGMENTS[$segment])) {
            $carry[] = $segment;
        }

        return $carry;
    }

    /**
     * Resolves an URI path and query component.
     *
     * @return array{0:string, 1:string|null}
     */
    private static function resolvePathAndQuery(
        Psr7UriInterface|UriInterface $uri,
        Psr7UriInterface|UriInterface $baseUri
    ): array {
        $targetPath = $uri->getPath();
        $targetQuery = $uri->getQuery();
        $null = $uri instanceof Psr7UriInterface ? '' : null;
        $baseNull = $baseUri instanceof Psr7UriInterface ? '' : null;

        if (str_starts_with($targetPath, '/')) {
            return [$targetPath, $targetQuery];
        }

        if ('' === $targetPath) {
            if ($null === $targetQuery) {
                $targetQuery = $baseUri->getQuery();
            }

            $targetPath = $baseUri->getPath();
            //@codeCoverageIgnoreStart
            //because some PSR-7 Uri implementations allow this RFC3986 forbidden construction
            if ($baseNull !== $baseUri->getAuthority() && !str_starts_with($targetPath, '/')) {
                $targetPath = '/'.$targetPath;
            }
            //@codeCoverageIgnoreEnd

            return [$targetPath, $targetQuery];
        }

        $base_path = $baseUri->getPath();
        if ($baseNull !== $baseUri->getAuthority() && '' === $base_path) {
            $targetPath = '/'.$targetPath;
        }

        if ('' !== $base_path) {
            $segments = explode('/', $base_path);
            array_pop($segments);
            if ([] !== $segments) {
                $targetPath = implode('/', $segments).'/'.$targetPath;
            }
        }

        return [$targetPath, $targetQuery];
    }

    /**
     * Relativizes a URI according to a base URI.
     *
     * This method MUST retain the state of the submitted URI instance, and return
     * an URI instance of the same type that contains the applied modifications.
     *
     * This method MUST be transparent when dealing with error and exceptions.
     * It MUST not alter of silence them apart from validating its own parameters.
     */
    public static function relativize(
        Psr7UriInterface|UriInterface|Stringable|string $uri,
        Psr7UriInterface|UriInterface|Stringable|string $baseUri
    ): Psr7UriInterface|UriInterface {
        $uri = self::filterUri($uri);
        $baseUri = self::filterUri($baseUri);

        $uri = self::formatHost($uri);
        $baseUri = self::formatHost($baseUri);
        if (!self::isRelativizable($uri, $baseUri)) {
            return $uri;
        }

        $null = $uri instanceof Psr7UriInterface ? '' : null;
        $uri = $uri->withScheme($null)->withPort(null)->withUserInfo($null)->withHost($null);
        $targetPath = $uri->getPath();
        if ($targetPath !== $baseUri->getPath()) {
            return $uri->withPath(self::relativizePath($targetPath, $baseUri->getPath()));
        }

        if (self::componentEquals('query', $uri, $baseUri)) {
            return $uri->withPath('')->withQuery($null);
        }

        if ($null === $uri->getQuery()) {
            return $uri->withPath(self::formatPathWithEmptyBaseQuery($targetPath));
        }

        return $uri->withPath('');
    }

    /**
     * Tells whether the component value from both URI object equals.
     */
    private static function componentEquals(
        string $property,
        Psr7UriInterface|UriInterface $uri,
        Psr7UriInterface|UriInterface $baseUri
    ): bool {
        return self::getComponent($property, $uri) === self::getComponent($property, $baseUri);
    }

    /**
     * Returns the component value from the submitted URI object.
     */
    private static function getComponent(string $property, Psr7UriInterface|UriInterface $uri): ?string
    {
        $component = match ($property) {
            'query' => $uri->getQuery(),
            'authority' => $uri->getAuthority(),
            default => $uri->getScheme(), //scheme
        };

        if ($uri instanceof Psr7UriInterface && '' === $component) {
            return null;
        }

        return $component;
    }

    /**
     * Filter the URI object.
     */
    private static function formatHost(Psr7UriInterface|UriInterface $uri): Psr7UriInterface|UriInterface
    {
        if (!$uri instanceof Psr7UriInterface) {
            return $uri;
        }

        $host = $uri->getHost();
        if ('' === $host) {
            return $uri;
        }

        return $uri->withHost((string) Uri::fromComponents(['host' => $host])->getHost());
    }

    /**
     * Tells whether the submitted URI object can be relativized.
     */
    private static function isRelativizable(
        Psr7UriInterface|UriInterface $uri,
        Psr7UriInterface|UriInterface $baseUri
    ): bool {
        return !UriInfo::isRelativePath($uri)
            && self::componentEquals('scheme', $uri, $baseUri)
            && self::componentEquals('authority', $uri, $baseUri);
    }

    /**
     * Relatives the URI for an authority-less target URI.
     */
    private static function relativizePath(string $path, string $basePath): string
    {
        $baseSegments = self::getSegments($basePath);
        $targetSegments = self::getSegments($path);
        $targetBasename = array_pop($targetSegments);
        array_pop($baseSegments);
        foreach ($baseSegments as $offset => $segment) {
            if (!isset($targetSegments[$offset]) || $segment !== $targetSegments[$offset]) {
                break;
            }
            unset($baseSegments[$offset], $targetSegments[$offset]);
        }
        $targetSegments[] = $targetBasename;

        return self::formatPath(
            str_repeat('../', count($baseSegments)).implode('/', $targetSegments),
            $basePath
        );
    }

    /**
     * returns the path segments.
     *
     * @return string[]
     */
    private static function getSegments(string $path): array
    {
        if ('' !== $path && '/' === $path[0]) {
            $path = substr($path, 1);
        }

        return explode('/', $path);
    }

    /**
     * Formatting the path to keep a valid URI.
     */
    private static function formatPath(string $path, string $basePath): string
    {
        if ('' === $path) {
            return in_array($basePath, ['', '/'], true) ? $basePath : './';
        }

        if (false === ($colonPosition = strpos($path, ':'))) {
            return $path;
        }

        $slashPosition = strpos($path, '/');
        if (false === $slashPosition || $colonPosition < $slashPosition) {
            return "./$path";
        }

        return $path;
    }

    /**
     * Formatting the path to keep a resolvable URI.
     */
    private static function formatPathWithEmptyBaseQuery(string $path): string
    {
        $targetSegments = self::getSegments($path);
        /** @var string $basename */
        $basename = end($targetSegments);

        return '' === $basename ? './' : $basename;
    }
}
