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
use Traversable;

/**
 * A Class to manage schemes registry
 *
 * @package League.url
 * @since 4.0.0
 */
class SchemeRegistry implements Interfaces\SchemeRegistry
{
    /**
     * Trait for Collection type Component
     */
    use Url\Utilities\CollectionTrait;

    /**
     * Standard ports for known schemes
     *
     * @var array
     */
    protected static $defaultSchemes = [
        'file'   => null,
        'ftp'    => 21,
        'ftps'   => 989,
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
            $this->data[$this->validateOffset($scheme)] = (new Url\Port($port))->toInt();
        }
        ksort($this->data, SORT_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator(array_map(function ($port) {
            return new Url\Port($port);
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

        return array_keys($this->data, (new Url\Port(func_get_arg(0)))->toInt(), true);
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

        return new Url\Port($this->data[$scheme]);
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
