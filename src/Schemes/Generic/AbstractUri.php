<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Schemes\Generic;

use Exception;
use InvalidArgumentException;
use League\Uri\Interfaces\Schemes\Uri;
use League\Uri\Types\ImmutablePropertyTrait;
use League\Uri\UriParser;
use Psr\Http\Message\UriInterface;
use RuntimeException;

/**
 * common URI Object properties and methods
 *
 * @package League.uri
 * @since   4.0.0
 *
 * @property-read \League\Uri\Interfaces\Components\Scheme   $scheme
 * @property-read \League\Uri\Interfaces\Components\UserInfo $userInfo
 * @property-read \League\Uri\Interfaces\Components\Host     $host
 * @property-read \League\Uri\Interfaces\Components\Port     $port
 * @property-read \League\Uri\Interfaces\Components\Path     $path
 * @property-read \League\Uri\Interfaces\Components\Query    $query
 * @property-read \League\Uri\Interfaces\Components\Fragment $fragment
 */
abstract class AbstractUri
{
    use ImmutablePropertyTrait;

    use PathFormatterTrait;

    use HostModifierTrait;

    use QueryModifierTrait;

    /**
     * Scheme Component
     *
     * @var \League\Uri\Interfaces\Components\Scheme
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var \League\Uri\Interfaces\Components\UserInfo
     */
    protected $userInfo;

    /**
     * Port Component
     *
     * @var \League\Uri\Interfaces\Components\Port
     */
    protected $port;

    /**
     * Fragment Component
     *
     * @var \League\Uri\Interfaces\Components\Fragment
     */
    protected $fragment;

    /**
     * Supported Schemes
     *
     * @var array
     */
    protected static $supportedSchemes = [];

    /**
     * Check if a URI is valid
     *
     * @throws InvalidArgumentException If the scheme is not supported
     *
     * @return bool
     */
    abstract protected function isValid();

    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents((new UriParser())->parse($uri));
    }

    /**
     * Assert the object is valid
     *
     * @throws RuntimeException if the resulting URI is not valid
     */
    protected function assertValidObject()
    {
        if (!$this->isValid()) {
            throw new RuntimeException('The submitted properties will produce an invalid object');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        return $this->withProperty('scheme', $scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return $this->userInfo->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $pass = null)
    {
        if (null === $pass) {
            $pass = '';
        }
        $userInfo = $this->userInfo->withUser($user)->withPass($pass);

        if ($this->userInfo->getUser() == $userInfo->getUser()
            && $this->userInfo->getPass() == ($userInfo->getPass())
        ) {
            return $this;
        }
        $clone = clone $this;
        $clone->userInfo = $userInfo;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->hasStandardPort() ? null : $this->port->toInt();
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        return $this->withProperty('port', $port);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutDotSegments()
    {
        return $this->withProperty('path', $this->path->withoutDotSegments());
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        return $this->withProperty('fragment', $fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->scheme->getUriComponent().$this->getSchemeSpecificPart();
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort()
    {
        if ($this->port->isEmpty()) {
            return true;
        }

        if ($this->scheme->isEmpty()) {
            return false;
        }

        return static::$supportedSchemes[$this->scheme->__toString()] === $this->port->toInt();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $port = '';
        if (!$this->hasStandardPort()) {
            $port = $this->port->getUriComponent();
        }

        return $this->userInfo->getUriComponent().$this->host->getUriComponent().$port;
    }


    /**
     * {@inheritdoc}
     */
    public function getSchemeSpecificPart()
    {
        $auth = $this->getAuthority();
        if (!empty($auth)) {
            $auth = '//'.$auth;
        }

        return $auth
            .$this->formatPath($this->path->getUriComponent(), (bool) $auth)
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return (new UriParser())->parse($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs($uri)
    {
        if (!$uri instanceof UriInterface && !$uri instanceof Uri) {
            throw new InvalidArgumentException(
                'You must provide an object implementing the `Psr\Http\Message\UriInterface` or
                the `League\Uri\Interfaces\Schemes\Uri` interface'
            );
        }

        try {
            return static::createFromComponents((new UriParser())->parse($uri->__toString()))
                ->hostToAscii()->ksortQuery()->withoutDotSegments()->__toString() === $this
                ->hostToAscii()->ksortQuery()->withoutDotSegments()->__toString();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if any generic URI is valid
     *
     * @return bool
     */
    protected function isValidGenericUri()
    {
        return (new UriParser())->isValidUri(
            $this->scheme->getUriComponent(),
            $this->getAuthority(),
            $this->path->getUriComponent()
        );
    }
}
