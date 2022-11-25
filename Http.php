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
use function is_scalar;

final class Http implements Psr7UriInterface, JsonSerializable
{
    private function __construct(private readonly UriInterface $uri)
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
        if (null === $uri->getScheme() && '' === $uri->getHost()) {
            throw new SyntaxError('An URI without scheme can not contains a empty host string according to PSR-7: '.$uri);
        }

        $port = $uri->getPort();
        if (null !== $port && ($port < 0 || $port > 65535)) {
            throw new SyntaxError('The URI port is outside the established TCP and UDP port ranges: '.$uri);
        }
    }

    /**
     * @param array{uri:UriInterface} $components
     */
    public static function __set_state(array $components): self
    {
        return new self($components['uri']);
    }

    /**
     * Create a new instance from a string.
     */
    public static function createFromString(Stringable|UriInterface|String $uri = ''): self
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
        Stringable|UriInterface|String $uri,
        Stringable|UriInterface|String $base_uri = null
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

    /**
     * {@inheritDoc}
     */
    public function getScheme(): string
    {
        return (string) $this->uri->getScheme();
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority(): string
    {
        return (string) $this->uri->getAuthority();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo(): string
    {
        return (string) $this->uri->getUserInfo();
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): string
    {
        return (string) $this->uri->getHost();
    }

    /**
     * {@inheritDoc}
     */
    public function getPort(): ?int
    {
        return $this->uri->getPort();
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->uri->getPath();
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): string
    {
        return (string) $this->uri->getQuery();
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment(): string
    {
        return (string) $this->uri->getFragment();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->uri->__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): string
    {
        return $this->uri->__toString();
    }

    /**
     * Safely stringify input when possible for League UriInterface compatibility.
     *
     * @throws SyntaxError
     */
    private function filterInput(mixed $str): string|null
    {
        if (!is_scalar($str) && !$str instanceof Stringable) {
            throw new SyntaxError('The component must be a string, a scalar or a Stringable object; `'.gettype($str).'` given.');
        }

        $str = (string) $str;
        if ('' === $str) {
            return null;
        }

        return $str;
    }

    private function newInstance(UriInterface $uri): self
    {
        if ((string) $uri === (string) $this->uri) {
            return $this;
        }

        return new self($uri);
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme($scheme): self
    {
        return $this->newInstance($this->uri->withScheme($this->filterInput($scheme)));
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo($user, $password = null): self
    {
        return $this->newInstance($this->uri->withUserInfo($this->filterInput($user), $password));
    }

    /**
     * {@inheritDoc}
     */
    public function withHost($host): self
    {
        return $this->newInstance($this->uri->withHost($this->filterInput($host)));
    }

    /**
     * {@inheritDoc}
     */
    public function withPort($port): self
    {
        return $this->newInstance($this->uri->withPort($port));
    }

    /**
     * {@inheritDoc}
     */
    public function withPath($path): self
    {
        return $this->newInstance($this->uri->withPath($path));
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery($query): self
    {
        return $this->newInstance($this->uri->withQuery($this->filterInput($query)));
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment($fragment): self
    {
        return $this->newInstance($this->uri->withFragment($this->filterInput($fragment)));
    }
}
