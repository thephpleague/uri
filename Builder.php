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

use League\Uri\Contracts\Conditionable;
use League\Uri\Contracts\UriComponentInterface;
use League\Uri\Exceptions\SyntaxError;
use SensitiveParameter;
use Stringable;
use Uri\Rfc3986\Uri as Rfc3986Uri;
use Uri\WhatWg\Url as WhatWgUrl;

use function is_bool;
use function str_replace;
use function strpos;

final class Builder implements Conditionable
{
    public function __construct(
        private ?string $scheme = null,
        private ?string $username = null,
        #[SensitiveParameter] private ?string $password = null,
        private ?string $host = null,
        private ?int $port = null,
        private ?string $path = null,
        private ?string $query = null,
        private ?string $fragment = null,
    ) {
        $this
            ->scheme($scheme)
            ->userInfo($username, $password)
            ->host($host)
            ->port($port)
            ->path($path)
            ->query($query)
            ->fragment($fragment);
    }

    /**
     * Puts back the Builder in a freshly created state.
     */
    public function reset(): self
    {
        $this->scheme = null;
        $this->username = null;
        $this->password = null;
        $this->host = null;
        $this->port = null;
        $this->path = null;
        $this->query = null;
        $this->fragment = null;

        return $this;
    }

    public function when(callable|bool $condition, callable $onSuccess, ?callable $onFail = null): static
    {
        if (!is_bool($condition)) {
            $condition = $condition($this);
        }

        return match (true) {
            $condition => $onSuccess($this),
            null !== $onFail => $onFail($this),
            default => $this,
        } ?? $this;
    }

    /**
     * @throws SyntaxError
     */
    public function scheme(Stringable|string|null $scheme): self
    {
        $scheme = $this->filterString($scheme);
        if ($scheme !== $this->scheme) {
            UriString::isValidScheme($scheme) || throw new SyntaxError('The scheme `'.$scheme.'` is invalid.');

            $this->scheme = $scheme;
        }

        return $this;
    }

    public function userInfo(
        Stringable|string|null $user,
        #[SensitiveParameter] Stringable|string|null $password = null
    ): static {
        $username = Encoder::encodeUser($this->filterString($user));
        $password = Encoder::encodePassword($this->filterString($password));
        if ($username !== $this->username || $password !== $this->password) {
            $this->username = $username;
            $this->password = $password;
        }

        return $this;
    }

    /**
     * @throws SyntaxError
     */
    public function host(Stringable|string|null $host): self
    {
        $host = $this->filterString($host);
        if ($host !== $this->host) {
            null === $host
            || HostRecord::isValid($host)
            || throw new SyntaxError('The host `'.$host.'` is invalid.');

            $this->host = $host;
        }

        return $this;
    }

    /**
     * @throws SyntaxError
     */
    public function port(?int $port): self
    {
        if ($port !== $this->port) {
            null === $port
            || ($port >= 0 && $port < 65535)
            || throw new SyntaxError('The port value must be null or an integer between 0 and 65535.');

            $this->port = $port;
        }

        return $this;
    }

    /**
     * @throws SyntaxError
     */
    public function path(Stringable|string|null $path): self
    {
        if ($path !== $this->path) {
            $this->path = null !== $path ? Encoder::encodePath($this->filterString($path)) : null;
        }

        return $this;
    }

    /**
     * @throws SyntaxError
     */
    public function query(Stringable|string|null $query): self
    {
        if ($query !== $this->query) {
            $this->query = Encoder::encodeQueryOrFragment($this->filterString($query));
        }

        return $this;
    }

    /**
     * @throws SyntaxError
     */
    public function fragment(Stringable|string|null $fragment): self
    {
        if ($fragment !== $this->fragment) {
            $this->fragment = Encoder::encodeQueryOrFragment($this->filterString($fragment));
        }

        return $this;
    }

    public function build(Rfc3986Uri|WhatWgUrl|Stringable|string|null $baseUri = null): Uri
    {
        $authority = $this->buildAuthority();
        $path = $this->buildPath($authority);
        $uriString = UriString::buildUri(
            $this->scheme,
            $authority,
            $path,
            Encoder::encodeQueryOrFragment($this->query),
            Encoder::encodeQueryOrFragment($this->fragment)
        );

        return Uri::new(null === $baseUri ? $uriString : UriString::resolve($uriString, match (true) {
            $baseUri instanceof Rfc3986Uri => $baseUri->toString(),
            $baseUri instanceof WhatWgUrl => $baseUri->toAsciiString(),
            default => (string) $baseUri,
        }));
    }

    /**
     * @throws SyntaxError
     */
    private function buildAuthority(): ?string
    {
        if (null === $this->host) {
            (null === $this->username && null === $this->password && null === $this->port)
            || throw new SyntaxError('The User Information and/or the Port component(s) are set without a Host component being present.');

            return null;
        }

        $authority = $this->host;
        if (null !== $this->username || null !== $this->password) {
            $userInfo = Encoder::encodeUser($this->username);
            if (null !== $this->password) {
                $userInfo .= ':'.Encoder::encodePassword($this->password);
            }

            $authority = $userInfo.'@'.$authority;
        }

        if (null !== $this->port) {
            return $authority.':'.$this->port;
        }

        return $authority;
    }

    /**
     * @throws SyntaxError
     */
    private function buildPath(?string $authority): ?string
    {
        if (null === $this->path || '' === $this->path) {
            return $this->path;
        }

        $path = Encoder::encodePath($this->path);
        if (null !== $authority) {
            // If there is an authority, the path must start with a `/`
            return str_starts_with($path, '/') ? $path : '/'.$path;
        }

        // If there is no authority, the path cannot start with `//`
        if (str_starts_with($path, '//')) {
            return '/.'.$path;
        }

        $colonPos = strpos($path, ':');
        if (false !== $colonPos && null === $this->scheme) {
            // In the absence of a scheme and of an authority,
            // the first path segment cannot contain a colon (":") character.'
            $slashPos = strpos($path, '/');
            (false !== $slashPos && $colonPos > $slashPos) || throw new SyntaxError(
                'In absence of the scheme and authority components, the first path segment cannot contain a colon (":") character.'
            );
        }

        return $path;
    }

    /**
     * Filter a string.
     *
     * @throws SyntaxError if the submitted data cannot be converted to string
     */
    private function filterString(Stringable|string|null $str): ?string
    {
        if (null === $str) {
            return null;
        }

        if ($str instanceof UriComponentInterface) {
            return $str->value();
        }

        $str = str_replace(' ', '%20', (string) $str);

        return UriString::containsRfc3987Chars($str)
            ? $str
            : throw new SyntaxError('The component value `'.$str.'` contains invalid characters.');
    }
}
