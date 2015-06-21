<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url\Services;

use ArrayIterator;
use InvalidArgumentException;
use League\Url;
use League\Url\Interfaces;

/**
 * A Class to manage schemes registry
 *
 * @package League.url
 * @since 4.0.0
 */
class SchemeRegistry implements Interfaces\SchemeRegistry
{
    /**
     * Standard ports for known schemes
     *
     * @var array
     */
    protected static $defaultSchemes = [
        'file'   => null,
        'ftp'    => 21,
        'gopher' => 70,
        'http'   => 80,
        'https'  => 443,
        'ldap'   => 389,
        'ldaps'  => 636,
        'nntp'   => 119,
        'snews'  => 563,
        'ssh'    => 22,
        'telnet' => 23,
        'wais'   => 210,
        'ws'     => 80,
        'wss'    => 443,
    ];

    /**
     * Registered scheme
     *
     * @var array
     */
    protected $data = [];

    /**
     * New Instance
     */
    public function __construct()
    {
        $this->data = static::$defaultSchemes;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function has($scheme)
    {
        $scheme = $this->formatScheme($scheme);
        return empty($scheme) || array_key_exists($scheme, $this->data);
    }

    /**
     * Validate Scheme syntax according to RFC3986
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the submitted scheme is invalid
     *
     * @return string
     */
    protected function formatScheme($scheme)
    {
        if (!empty($scheme) && !preg_match('/^[a-z][-a-z0-9+.]+$/i', $scheme)) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $scheme));
        }

        return strtolower($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function add($scheme, $port = null)
    {
        $scheme = $this->formatScheme($scheme);
        if (empty($scheme) || array_key_exists($scheme, $this->data)) {
            throw new InvalidArgumentException(sprintf("The submitted scheme '%s' is already defined", $scheme));
        }

        if (!$port instanceof Interfaces\Port) {
            $port = new Url\Port($port);
        }

        $this->data[$scheme] = $port->toInt();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($scheme)
    {
        $scheme = $this->formatScheme($scheme);
        if (empty($scheme) || isset(static::$defaultSchemes[$scheme])) {
            throw new InvalidArgumentException(sprintf("The submitted scheme '%s' is already defined", $scheme));
        }
        unset($this->data[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStandardPort($scheme)
    {
        $scheme = $this->formatScheme($scheme);
        if (empty($scheme)) {
            return new Url\Port();
        }

        if (!$this->has($scheme)) {
            throw new InvalidArgumentException(sprintf("Unknown submitted scheme: '%s'", $scheme));
        }

        return new Url\Port($this->data[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function isStandardPort($scheme, $port)
    {
        if (!$port instanceof Interfaces\Port) {
            $port = new Url\Port($port);
        }

        return $port->isEmpty() || $port->sameValueAs($this->getStandardPort($scheme));
    }
}
