<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Types\ImmutablePropertyTrait;
use League\Uri\UriParser;
use RuntimeException;

/**
 * common URI Object properties and methods
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
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
     * Check if any generic URI is valid
     *
     * @return bool
     */
    protected function isValidGenericUri()
    {
        $path = $this->path->getUriComponent();
        if (false === strpos($path, ':')) {
            return true;
        }

        $path = explode(':', $path);
        $path = array_shift($path);
        $str = $this->scheme->getUriComponent().$this->getAuthority();

        return (!(empty($str) && strpos($path, '/') === false));
    }
}
