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
use League\Uri\Contract\UriInterface;
use League\Uri\Exception\SyntaxError;
use Psr\Http\Message\UriInterface as Psr7UriInterface;
use function is_scalar;
use function method_exists;
use function sprintf;

final class Http implements Psr7UriInterface, JsonSerializable
{
    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * New instance.
     */
    public function __construct(UriInterface $uri)
    {
        $this->validate($uri);
        $this->uri = $uri;
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
            throw new SyntaxError(sprintf('an URI without scheme can not contains a empty host string according to PSR-7: %s', (string) $uri));
        }

        $port = $uri->getPort();
        if (null !== $port && ($port < 0 || $port > 65535)) {
            throw new SyntaxError(sprintf('The URI port is outside the established TCP and UDP port ranges: %s', (string) $uri->getPort()));
        }
    }

    /**
     * Static method called by PHP's var export.
     *
     * @return static
     */
    public static function __set_state(array $components): self
    {
        return new self($components['uri']);
    }

    /**
     * Create a new instance from a string.
     *
     * @param string|mixed $uri
     */
    public static function createFromString($uri = ''): self
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
     * Create a new instance from a URI and a Base URI.
     *
     * The returned URI must be absolute.
     *
     * @param mixed $uri      the input URI to create
     * @param mixed $base_uri the base URI used for reference
     */
    public static function create($uri, $base_uri = null): self
    {
        return new self(Uri::create($uri, $base_uri));
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return (string) $this->uri->getScheme();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        return (string) $this->uri->getAuthority();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        return (string) $this->uri->getUserInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return (string) $this->uri->getHost();
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->uri->getPort();
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->uri->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return (string) $this->uri->getQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return (string) $this->uri->getFragment();
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme): self
    {
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
     * @param mixed $str the value to evaluate as a string
     *
     * @throws SyntaxError if the submitted data can not be converted to string
     *
     * @return string|mixed
     */
    private function filterInput($str)
    {
        if (is_scalar($str) || method_exists($str, '__toString')) {
            return (string) $str;
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function withPort($port): self
    {
        $uri = $this->uri->withPort($port);
        if ($uri->getPort() === $this->uri->getPort()) {
            return $this;
        }

        return new self($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path): self
    {
        $uri = $this->uri->withPath($path);
        if ($uri->getPath() === $this->uri->getPath()) {
            return $this;
        }

        return new self($uri);
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->uri->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): string
    {
        return $this->uri->__toString();
    }
}
