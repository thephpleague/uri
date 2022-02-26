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

use JsonSerializable;
use League\Uri\Contracts\UriInterface;
use League\Uri\Exceptions\SyntaxError;
use Psr\Http\Message\UriInterface as Psr7UriInterface;
use Stringable;
use function sprintf;

final class Http implements Psr7UriInterface, JsonSerializable
{
    private function __construct(private UriInterface $uri)
    {
        $this->validate($this->uri);
    }

    /**
     * Validate the submitted uri against PSR-7 UriInterface.
     *
     * @throws SyntaxError if the given URI does not follow PSR-7 UriInterface rules
     */
    private function validate(UriInterface $uri): void
    {
        $scheme = $uri->getScheme();
        if (null === $scheme && '' === $uri->getHost()) {
            throw new SyntaxError(sprintf('an URI without scheme can not contains a empty host string according to PSR-7: %s', $uri->__toString()));
        }

        $port = $uri->getPort();
        if (null !== $port && ($port < 0 || $port > 65535)) {
            throw new SyntaxError(sprintf('The URI port is outside the established TCP and UDP port ranges: %d', $uri->getPort()));
        }
    }

    /**
     * Static method called by PHP's var export.
     */
    public static function __set_state(array $components): self
    {
        return new self($components['uri']);
    }

    /**
     * Create a new instance from a string.
     *
     */
    public static function createFromString(Stringable|string $uri = ''): self
    {
        return new self(Uri::createFromString($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts.
     *
     * @param array $components a hash representation of the URI similar
     *                          to PHP parse_url function result
     */
    public static function createFromComponents(array $components): self
    {
        return new self(Uri::createFromComponents($components));
    }

    /**
     * Create a new instance from the environment.
     */
    public static function createFromServer(array $server): self
    {
        return new self(Uri::createFromServer($server));
    }

    /**
     * Create a new instance from a URI and a Base URI.
     *
     * The returned URI must be absolute.
     */
    public static function createFromBaseUri(
        Psr7UriInterface|UriInterface|Stringable|string $uri,
        Psr7UriInterface|UriInterface|Stringable|string|null $base_uri = null
    ): self {
        return new self(Uri::createFromBaseUri($uri, $base_uri));
    }

    /**
     * Create a new instance from a URI object.
     */
    public static function createFromUri(Psr7UriInterface|UriInterface $uri): self
    {
        if ($uri instanceof UriInterface) {
            return new self($uri);
        }

        return new self(Uri::createFromUri($uri));
    }


    public function getScheme(): string
    {
        return (string) $this->uri->getScheme();
    }


    public function getAuthority(): string
    {
        return (string) $this->uri->getAuthority();
    }


    public function getUserInfo(): string
    {
        return (string) $this->uri->getUserInfo();
    }


    public function getHost(): string
    {
        return (string) $this->uri->getHost();
    }


    public function getPort(): int|null
    {
        return $this->uri->getPort();
    }


    public function getPath(): string
    {
        return $this->uri->getPath();
    }


    public function getQuery(): string
    {
        return (string) $this->uri->getQuery();
    }


    public function getFragment(): string
    {
        return (string) $this->uri->getFragment();
    }


    public function withScheme($scheme): self
    {
        /** @var string $scheme */
        $scheme = $this->filterInput($scheme);
        if ('' === $scheme) {
            $scheme = null;
        }

        $uri = $this->uri->withScheme($scheme);
        if ($uri->getScheme() === $this->uri->getScheme()) {
            return $this;
        }

        return new self($uri);
    }

    /**
     * Safely stringify input when possible.
     *
     * @throws SyntaxError if the submitted data can not be converted to string
     *
     */
    private function filterInput(Stringable|int|string $str): string
    {
        return (string) $str;
    }


    public function withUserInfo($user, $password = null): self
    {
        $user = $this->filterInput($user);
        if ('' === $user) {
            $user = null;
        }

        $uri = $this->uri->withUserInfo($user, $password);
        if ($uri->getUserInfo() === $this->uri->getUserInfo()) {
            return $this;
        }

        return new self($uri);
    }


    public function withHost($host): self
    {
        $host = $this->filterInput($host);
        if ('' === $host) {
            $host = null;
        }

        $uri = $this->uri->withHost($host);
        if ($uri->getHost() === $this->uri->getHost()) {
            return $this;
        }

        return new self($uri);
    }


    public function withPort($port): self
    {
        $uri = $this->uri->withPort($port);
        if ($uri->getPort() === $this->uri->getPort()) {
            return $this;
        }

        return new self($uri);
    }


    public function withPath($path): self
    {
        $uri = $this->uri->withPath($path);
        if ($uri->getPath() === $this->uri->getPath()) {
            return $this;
        }

        return new self($uri);
    }


    public function withQuery($query): self
    {
        $query = $this->filterInput($query);
        if ('' === $query) {
            $query = null;
        }

        $uri = $this->uri->withQuery($query);
        if ($uri->getQuery() === $this->uri->getQuery()) {
            return $this;
        }

        return new self($uri);
    }


    public function withFragment($fragment): self
    {
        $fragment = $this->filterInput($fragment);
        if ('' === $fragment) {
            $fragment = null;
        }

        $uri = $this->uri->withFragment($fragment);
        if ($uri->getFragment() === $this->uri->getFragment()) {
            return $this;
        }

        return new self($uri);
    }


    public function __toString(): string
    {
        return $this->uri->__toString();
    }


    public function jsonSerialize(): string
    {
        return $this->uri->__toString();
    }
}
