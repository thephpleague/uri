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

final class BaseUri implements Stringable
{
    /**
     * @var array<string,int>
     */
    private const DOT_SEGMENTS = ['.' => 1, '..' => 1];

    public readonly ?UriInterface $origin;

    private function __construct(
        public readonly Psr7UriInterface|UriInterface $value
    ) {
        $origin = UriInfo::getOrigin($this->value);
        if (null !== $origin) {
            $this->origin = Uri::new($origin);
        }
    }

    public static function new(Stringable|string $baseUri): self
    {
        return new self(self::filterUri($baseUri));
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }

    /**
     * Input URI normalization to allow Stringable and string URI.
     */
    private static function filterUri(Stringable|string $uri): Psr7UriInterface|UriInterface
    {
        return match (true) {
            $uri instanceof Psr7UriInterface, $uri instanceof UriInterface => $uri,
            default => Uri::new($uri),
        };
    }

    /**
     * Resolves a URI against a base URI using RFC3986 rules.
     *
     * This method MUST retain the state of the submitted URI instance, and return
     * a URI instance of the same type that contains the applied modifications.
     *
     * This method MUST be transparent when dealing with error and exceptions.
     * It MUST not alter or silence them apart from validating its own parameters.
     */
    public function resolve(Stringable|string $uri): self
    {
        $uri = self::filterUri($uri);
        $null = $uri instanceof Psr7UriInterface ? '' : null;

        if ($null !== $uri->getScheme()) {
            return new self($uri
                ->withPath(self::removeDotSegments($uri->getPath())));
        }

        if ($null !== $uri->getAuthority()) {
            $scheme = $this->value->getScheme();
            if (null === $scheme || '' === $null) {
                $scheme = '';
            }

            return new self($uri
                ->withScheme($scheme)
                ->withPath(self::removeDotSegments($uri->getPath())));
        }

        $user = $null;
        $pass = null;
        $userInfo = $this->value->getUserInfo();
        if (null !== $userInfo) {
            [$user, $pass] = explode(':', $userInfo, 2) + [1 => null];
        }

        [$path, $query] = $this->resolvePathAndQuery($uri);

        return new self($uri
            ->withPath($this->removeDotSegments($path))
            ->withQuery($query)
            ->withHost($this->value->getHost())
            ->withPort($this->value->getPort())
            ->withUserInfo((string) $user, $pass)
            ->withScheme($this->value->getScheme()))
        ;
    }

    /**
     * Remove dot segments from the URI path.
     */
    private function removeDotSegments(string $path): string
    {
        if (!str_contains($path, '.')) {
            return $path;
        }

        $oldSegments = explode('/', $path);
        $newPath = implode('/', array_reduce($oldSegments, self::reducer(...), []));
        if (isset(self::DOT_SEGMENTS[end($oldSegments)])) {
            $newPath .= '/';
        }

        // @codeCoverageIgnoreStart
        // added because some PSR-7 implementations do not respect RFC3986
        if (str_starts_with($path, '/') && !str_starts_with($newPath, '/')) {
            return '/'.$newPath;
        }
        // @codeCoverageIgnoreEnd

        return $newPath;
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
    private function resolvePathAndQuery(Psr7UriInterface|UriInterface $uri): array
    {
        $targetPath = $uri->getPath();
        $null = $uri instanceof Psr7UriInterface ? '' : null;

        if (str_starts_with($targetPath, '/')) {
            return [$targetPath, $uri->getQuery()];
        }

        if ('' === $targetPath) {
            $targetQuery = $uri->getQuery();
            if ($null === $targetQuery) {
                $targetQuery = $this->value->getQuery();
            }

            $targetPath = $this->value->getPath();
            //@codeCoverageIgnoreStart
            //because some PSR-7 Uri implementations allow this RFC3986 forbidden construction
            if (null !== $this->value->getAuthority() && !str_starts_with($targetPath, '/')) {
                $targetPath = '/'.$targetPath;
            }
            //@codeCoverageIgnoreEnd

            return [$targetPath, $targetQuery];
        }

        $basePath = $this->value->getPath();
        if (null !== $this->value->getAuthority() && '' === $basePath) {
            $targetPath = '/'.$targetPath;
        }

        if ('' !== $basePath) {
            $segments = explode('/', $basePath);
            array_pop($segments);
            if ([] !== $segments) {
                $targetPath = implode('/', $segments).'/'.$targetPath;
            }
        }

        return [$targetPath, $uri->getQuery()];
    }

    /**
     * Relativize a URI according to a base URI.
     *
     * This method MUST retain the state of the submitted URI instance, and return
     * a URI instance of the same type that contains the applied modifications.
     *
     * This method MUST be transparent when dealing with error and exceptions.
     * It MUST not alter of silence them apart from validating its own parameters.
     */
    public function relativize(Stringable|string $uri): self
    {
        $uri = self::formatHost(self::filterUri($uri));
        if (!$this->isRelativizable($uri)) {
            return new self($uri);
        }

        $null = $uri instanceof Psr7UriInterface ? '' : null;
        $uri = $uri->withScheme($null)->withPort(null)->withUserInfo($null)->withHost($null);
        $targetPath = $uri->getPath();
        $basePath = $this->value->getPath();

        return new self(match (true) {
            $targetPath !== $basePath => $uri->withPath(self::relativizePath($targetPath, $basePath)),
            self::componentEquals('query', $uri) => $uri->withPath('')->withQuery($null),
            $null === $uri->getQuery() => $uri->withPath(self::formatPathWithEmptyBaseQuery($targetPath)),
            default => $uri->withPath(''),
        });
    }

    /**
     * Tells whether the component value from both URI object equals.
     */
    private function componentEquals(string $property, Psr7UriInterface|UriInterface $uri): bool
    {
        return self::getComponent($property, $uri) === self::getComponent($property, $this->value);
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
    private function isRelativizable(Psr7UriInterface|UriInterface $uri): bool
    {
        return !UriInfo::isRelativePath($uri)
            && self::componentEquals('scheme', $uri)
            && self::componentEquals('authority', $uri);
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

    /**
     * Tells whether two URI do not share the same origin.
     *
     * @see UriInfo::getOrigin()
     */
    public function isCrossOrigin(Stringable|string $uri): bool
    {
        return null === $this->origin
            || null === ($uriString = UriInfo::getOrigin($uri))
            || $uriString !== $this->origin->toString();
    }
}
