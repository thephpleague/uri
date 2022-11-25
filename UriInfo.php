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
use function explode;
use function implode;
use function preg_replace_callback;
use function rawurldecode;

final class UriInfo
{
    private const REGEXP_ENCODED_CHARS = ',%(2[D|E]|3\d|4[1-9|A-F]|5[\d|A|F]|6[1-9|A-F]|7[\d|E]),i';
    private const WHATWG_SPECIAL_SCHEMES = ['ftp', 'http', 'https', 'ws', 'wss'];

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }


    private static function emptyComponentValue(Psr7UriInterface|UriInterface $uri): ?string
    {
        return $uri instanceof Psr7UriInterface ? '' : null;
    }

    /**
     * Normalizes an URI for comparison.
     */
    private static function normalize(Psr7UriInterface|UriInterface $uri): Psr7UriInterface|UriInterface
    {
        $null = self::emptyComponentValue($uri);

        $path = $uri->getPath();
        if ('/' === ($path[0] ?? '') || '' !== $uri->getScheme().$uri->getAuthority()) {
            $path = UriResolver::resolve($uri, $uri->withPath('')->withQuery($null))->getPath();
        }

        $query = $uri->getQuery();
        $fragment = $uri->getFragment();
        $fragmentOrig = $fragment;
        $pairs = null === $query ? [] : explode('&', $query);
        sort($pairs, SORT_REGULAR);

        $replace = static fn (array $matches): string => rawurldecode($matches[0]);

        $retval = preg_replace_callback(self::REGEXP_ENCODED_CHARS, $replace, [$path, implode('&', $pairs), $fragment]);
        if (null !== $retval) {
            [$path, $query, $fragment] = $retval + ['', $null, $null];
        }

        if ($null !== $uri->getAuthority() && '' === $path) {
            $path = '/';
        }

        return $uri
            ->withHost(Uri::createFromComponents(['host' => $uri->getHost()])->getHost())
            ->withPath($path)
            ->withQuery([] === $pairs ? $null : $query)
            ->withFragment($null === $fragmentOrig ? $fragmentOrig : $fragment);
    }

    /**
     * Tells whether the URI represents an absolute URI.
     */
    public static function isAbsolute(Psr7UriInterface|UriInterface $uri): bool
    {
        return self::emptyComponentValue($uri) !== $uri->getScheme();
    }

    /**
     * Tell whether the URI represents a network path.
     */
    public static function isNetworkPath(Psr7UriInterface|UriInterface $uri): bool
    {
        $null = self::emptyComponentValue($uri);

        return $null === $uri->getScheme() && $null !== $uri->getAuthority();
    }

    /**
     * Tells whether the URI represents an absolute path.
     */
    public static function isAbsolutePath(Psr7UriInterface|UriInterface $uri): bool
    {
        $null = self::emptyComponentValue($uri);

        return $null === $uri->getScheme()
            && $null === $uri->getAuthority()
            && '/' === ($uri->getPath()[0] ?? '');
    }

    /**
     * Tell whether the URI represents a relative path.
     *
     */
    public static function isRelativePath(Psr7UriInterface|UriInterface $uri): bool
    {
        $null = self::emptyComponentValue($uri);

        return $null === $uri->getScheme()
            && $null === $uri->getAuthority()
            && '/' !== ($uri->getPath()[0] ?? '');
    }

    /**
     * Tells whether both URI refers to the same document.
     */
    public static function isSameDocument(Psr7UriInterface|UriInterface $uri, Psr7UriInterface|UriInterface $base_uri): bool
    {
        $uri = self::normalize($uri);
        $base_uri = self::normalize($base_uri);

        return (string) $uri->withFragment($uri instanceof Psr7UriInterface ? '' : null)
            === (string) $base_uri->withFragment($base_uri instanceof Psr7UriInterface ? '' : null);
    }

    /**
     * Returns the URI origin property as defined by WHATWG URL living standard.
     *
     * {@see https://url.spec.whatwg.org/#origin}
     *
     * For URI without a special scheme the method returns null
     * For URI with the file scheme the method will return null (as this is left to the implementation decision)
     * For URI with a special scheme the method returns the scheme followed by its authority (without the userinfo part)
     */
    public static function getOrigin(Psr7UriInterface|UriInterface $uri): ?string
    {
        $scheme = $uri->getScheme();
        if ('blob' === $scheme) {
            $uri = Uri::createFromString($uri->getPath());
            $scheme = $uri->getScheme();
        }

        if (in_array($scheme, self::WHATWG_SPECIAL_SCHEMES, true)) {
            $null = self::emptyComponentValue($uri);

            return (string) $uri->withFragment($null)->withQuery($null)->withPath('')->withUserInfo($null, null);
        }

        return null;
    }

    /**
     * Tells whether two URI do not share the same origin.
     *
     * @see UriInfo::getOrigin()
     */
    public static function isCrossOrigin(Psr7UriInterface|UriInterface $uri, Psr7UriInterface|UriInterface $base_uri): bool
    {
        return null === ($uriString = self::getOrigin(Uri::createFromUri($uri)))
            || null === ($baseUriString = self::getOrigin(Uri::createFromUri($base_uri)))
            || $uriString !== $baseUriString;
    }
}
