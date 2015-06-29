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
namespace League\Uri\Utilities;

use ArrayIterator;
use InvalidArgumentException;
use League\Uri\Interfaces\Collection;
use Traversable;

/**
 * Common methods for Collection objects
 *
 * @package League.url
 * @since  4.0.0
 */
trait CollectionTrait
{
    /**
     * The Component Data
     *
     * @var array
     */
    protected $data = [];

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
    public function toArray()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOffset($offset)
    {
        return array_key_exists($this->validateOffset($offset), $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsets()
    {
        if (0 == func_num_args()) {
            return array_keys($this->data);
        }

        return array_keys($this->data, func_get_arg(0), true);
    }

    /**
     * {@inheritdoc}
     */
    public function without($offsets)
    {
        if (is_callable($offsets)) {
            $offsets = array_filter(array_keys($this->data), $offsets);
        }

        if (!is_array($offsets)) {
            throw new InvalidArgumentException('You must give a callable or an array as only argument');
        }

        $data = $this->data;
        foreach ($offsets as $offset) {
            unset($data[$this->validateOffset($offset)]);
        }

        return $this->newCollectionInstance($data);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callable, $flag = Collection::FILTER_USE_VALUE)
    {
        if (!in_array($flag, [Collection::FILTER_USE_VALUE, Collection::FILTER_USE_KEY])) {
            throw new InvalidArgumentException('Unknown flag parameter please use one of the defined constant');
        }

        if ($flag == Collection::FILTER_USE_KEY) {
            return $this->filterByOffset($callable);
        }

        return $this->newCollectionInstance(array_filter($this->data, $callable));
    }

    /**
     * Return a new instance when needed
     *
     * @param array $data
     *
     * @return static
     */
    abstract protected function newCollectionInstance(array $data);

    /**
     * Filter The Collection according to its offsets
     *
     * @param callable $callable
     *
     * @return static
     */
    protected function filterByOffset(callable $callable)
    {
        $data = [];
        foreach (array_filter(array_keys($this->data), $callable) as $offset) {
            $data[$offset] = $this->data[$offset];
        }

        return $this->newCollectionInstance($data);
    }

    /**
     * Validate an Iterator or an array
     *
     * @param Traversable|array $data
     *
     * @throws InvalidArgumentException if the value can not be converted
     *
     * @return array
     */
    protected static function validateIterator($data)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data, true);
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Data passed to the method must be an array or a Traversable object');
        }

        return $data;
    }

    /**
     * Validate offset
     *
     * @param  int|string $offset
     *
     * @throws InvalidArgumentException if the offset is invalid
     *
     * @return int|string
     */
    protected function validateOffset($offset)
    {
        return $offset;
    }
}
