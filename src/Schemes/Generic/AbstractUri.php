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
use League\Uri\Interfaces\Fragment;
use League\Uri\Interfaces\Host;
use League\Uri\Interfaces\Path;
use League\Uri\Interfaces\Port;
use League\Uri\Interfaces\Query;
use League\Uri\Interfaces\Scheme;
use League\Uri\Interfaces\UserInfo;
use League\Uri\Types\ImmutablePropertyTrait;
use RuntimeException;

/**
 * common URI Object properties and methods
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class AbstractUri
{
    use ImmutablePropertyTrait;

    use PathFormatterTrait;

    use AuthorityValidatorTrait;

    /**
     * Host Component
     *
     * @var Host
     */
    protected $host;

    /**
     * Scheme Component
     *
     * @var Scheme
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var UserInfo
     */
    protected $userInfo;

    /**
     * Port Component
     *
     * @var Port
     */
    protected $port;

    /**
     * Path Component
     *
     * @var Path
     */
    protected $path;

    /**
     * Query Component
     *
     * @var Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var Fragment
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
            && $this->userInfo->getPass() == $userInfo->getPass()
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
    public function getHost()
    {
        return $this->host->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        return $this->withProperty('host', $host);
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
    public function getPath()
    {
        return $this->path->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        return $this->withProperty('path', $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        return $this->withProperty('query', $query);
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
     * Returns whether the standard port for the given scheme is used, when
     * the scheme is unknown or unsupported will the method return false
     *
     * @return bool
     */
    protected function hasStandardPort()
    {
        if (empty($this->port->getContent())) {
            return true;
        }

        if (empty($this->scheme->getContent())) {
            return false;
        }

        return isset(static::$supportedSchemes[$this->scheme->__toString()])
            && static::$supportedSchemes[$this->scheme->__toString()] === $this->port->toInt();
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

    /**
     * Tell whether the Hierarchical URI is valid
     *
     * @throws InvalidArgumentException If the Scheme is not supported
     *
     * @return bool
     */
    protected function isValidHierarchicalUri()
    {
        $this->assertSupportedScheme();

        return $this->isAuthorityValid();
    }

    /**
     * Assert whether the current scheme is supported by the URI object
     *
     * @throws InvalidArgumentException If the Scheme is not supported
     */
    protected function assertSupportedScheme()
    {
        $scheme = $this->getScheme();
        if (!empty($scheme) && !isset(static::$supportedSchemes[$scheme])) {
            throw new InvalidArgumentException('The submitted scheme is unsupported by '.get_class($this));
        }
    }
}
