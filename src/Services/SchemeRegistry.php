<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri\Services;

use ArrayIterator;
use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use Traversable;

/**
 * A class to manage schemes registry
 *
 * @package League.url
 * @since   4.0.0
 */
class SchemeRegistry implements Interfaces\SchemeRegistry
{
    /**
     * Collection Trait
     */
    use Uri\Utilities\CollectionTrait;

    /**
     * Standard ports for known schemes
     *
     * @var array
     */
    protected static $defaultSchemes = [
        'file'   => null,
        'ftp'    => 21,
        'http'   => 80,
        'https'  => 443,
        'ssh'    => 22,
        'ws'     => 80,
        'wss'    => 443,
    ];

    /**
     * New Instance
     *
     * The scheme must be valid according to RFC3986 rules
     * The port can be:
     * - a positive integer
     * - an Interfaces\Port object
     * - null
     *
     * <code>
     * <?php
     *  $registry = new SchemeRegistry(['http' => 80, 'ws' => 80, 'file' => null]);
     * ?>
     * </code>
     *
     * @param array scheme/standard port pair
     *
     * @throws InvalidArgumentException If the scheme or the port is invalid
     */
    public function __construct(array $data = [])
    {
        if (empty($data)) {
            $data = static::$defaultSchemes;
        }
        foreach ($data as $scheme => $port) {
            $this->data[$this->validateOffset($scheme)] = (new Uri\Port($port))->toInt();
        }
        ksort($this->data, SORT_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator(array_map(function ($port) {
            return new Uri\Port($port);
        }, $this->data));
    }

    /**
     * {@inheritdoc}
     */
    public function offsets()
    {
        if (0 == func_num_args()) {
            return array_keys($this->data);
        }

        return array_keys($this->data, (new Uri\Port(func_get_arg(0)))->toInt(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function newCollectionInstance(array $data)
    {
        if ($data == $this->data) {
            return $this;
        }

        return new static($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateOffset($scheme)
    {
        if (!preg_match('/^[a-z][-a-z0-9+.]+$/i', $scheme)) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $scheme));
        }

        return strtolower($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function getPort($scheme)
    {
        $scheme = $this->validateOffset($scheme);
        if (!$this->hasOffset($scheme)) {
            throw new InvalidArgumentException(sprintf("Unknown submitted scheme: '%s'", $scheme));
        }

        return new Uri\Port($this->data[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function merge($registry)
    {
        if (!$registry instanceof Interfaces\SchemeRegistry) {
            $registry = new static(static::validateIterator($registry));
        }

        if ($this->toArray() == $registry->toArray()) {
            return $this;
        }

        return new static(array_merge($this->toArray(), $registry->toArray()));
    }
}
