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

use Stringable;

/**
 * @deprecated since version 7.0.0
 * @codeCoverageIgnore
 * @see BaseUri
 */
final class UriInfo
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Tells whether the URI represents an absolute URI.
     */
    public static function isAbsolute(Stringable|string $uri): bool
    {
        return BaseUri::new($uri)->isAbsolute();
    }

    /**
     * Tell whether the URI represents a network path.
     */
    public static function isNetworkPath(Stringable|string $uri): bool
    {
        return BaseUri::new($uri)->isNetworkPath();
    }

    /**
     * Tells whether the URI represents an absolute path.
     */
    public static function isAbsolutePath(Stringable|string $uri): bool
    {
        return BaseUri::new($uri)->isAbsolutePath();
    }

    /**
     * Tell whether the URI represents a relative path.
     *
     */
    public static function isRelativePath(Stringable|string $uri): bool
    {
        return BaseUri::new($uri)->isRelativePath();
    }

    /**
     * Tells whether both URI refers to the same document.
     */
    public static function isSameDocument(Stringable|string $uri, Stringable|string $baseUri): bool
    {
        return BaseUri::new($baseUri)->isSameDocument($uri);
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
    public static function getOrigin(Stringable|string $uri): ?string
    {
        return BaseUri::new($uri)->origin()?->__toString();
    }

    /**
     * Tells whether two URI do not share the same origin.
     *
     * @see UriInfo::getOrigin()
     */
    public static function isCrossOrigin(Stringable|string $uri, Stringable|string $baseUri): bool
    {
        return BaseUri::new($baseUri)->isCrossOrigin($uri);
    }
}
