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
namespace League\Uri\Schemes;

use ArrayIterator;
use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;

/**
 * A class to manage schemes registry
 *
 * @package League.url
 * @since   4.0.0
 */
class Registry implements Interfaces\SchemeRegistry
{
    /**
     * Collection Trait
     */
    use Uri\Types\ImmutableCollection;

    /**
     * Scheme Validator Trait
     */
    use Validator;

    /**
     * {@inheritdoc}
     */
    protected function validateOffset($scheme)
    {
        return $this->validate($scheme);
    }

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
    public function __construct(array $data)
    {
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
    public function keys()
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
    public function getPort($scheme)
    {
        $scheme = $this->validateOffset($scheme);
        if (!$this->hasKey($scheme)) {
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

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Interfaces\SchemeRegistry $schemeRegistry)
    {
        return $this->toArray() === $schemeRegistry->toArray();
    }
}
