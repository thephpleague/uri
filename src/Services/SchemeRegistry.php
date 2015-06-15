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
use League\Url\Interfaces;
use League\Url\Port;

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
        'ftp'   => [21],
        'ftps'  => [989, 990],
        'https' => [443],
        'http'  => [80],
        'ws'    => [80],
        'wss'   => [443],
        ''      => [],
    ];

    /**
     * Registred scheme
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
        return isset($this->data[$this->formatScheme($scheme)]);
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
        if (empty($scheme)) {
            return '';
        }

        if (! filter_var($scheme, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-z][-a-z0-9+.]+$/i']])) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $scheme));
        }

        return strtolower($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function getStandardPorts($scheme)
    {
        $scheme = $this->formatScheme($scheme);
        if (! isset($this->data[$scheme])) {
            throw new InvalidArgumentException(sprintf("Unknown submitted scheme: '%s'", $scheme));
        }

        return array_map(function ($value) {
            return new Port($value);
        }, $this->data[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function isStandardPort($scheme, $port)
    {
        $scheme = $this->formatScheme($scheme);
        if (! isset($this->data[$scheme])) {
            throw new InvalidArgumentException(sprintf("Unknown scheme '%s'", $scheme));
        }

        if (! $port instanceof Interfaces\Port) {
            $port = new Port($port);
        }

        return $port->isEmpty() || in_array($port->toInt(), $this->data[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function add($scheme, $port = null)
    {
        $scheme = $this->formatScheme($scheme);
        $this->assertValidScheme($scheme);
        if (! isset($this->data[$scheme])) {
            $this->data[$scheme] = [];
        }
        if (! $port instanceof Interfaces\Port) {
            $port = new Port($port);
        }

        $this->data[$scheme][] = $port->toInt();
        sort($this->data[$scheme]);
    }

    /**
     * Restrict action on default Schemes
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the scheme is part
     *                                     of the defaults list of registered scheme
     */
    protected function assertValidScheme($scheme)
    {
        if (isset(static::$defaultSchemes[$scheme])) {
            throw new InvalidArgumentException(sprintf("The submitted scheme '%s' is already defined", $scheme));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($scheme)
    {
        $scheme = $this->formatScheme($scheme);
        $this->assertValidScheme($scheme);
        unset($this->data[$scheme]);
    }
}
