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
namespace League\Url\Util;

use ArrayIterator;
use InvalidArgumentException;
use Traversable;

/**
 * A trait with common methods for Collection like Component
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
     * is the CollectionComponent absolute
     *
     * @var boolean
     */
    protected $is_absolute = false;

    /**
     * Trait for ComponentTrait method
     */
    use ComponentTrait;

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
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function without(array $offsets)
    {
        $data = $this->data;
        foreach (array_unique($offsets) as $offset) {
            unset($data[$offset]);
        }

        return static::createFromArray($data, $this->is_absolute);
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

        if (! is_array($data)) {
            throw new InvalidArgumentException('Data passed to the method must be an array or a Traversable object');
        }

        return $data;
    }

    /**
     * Validate a component as a Interfaces\CollectionComponent object
     *
     * @param  mixed $component
     *
     * @throws InvalidArgumentException if the value can not be converted
     *
     * @return Interfaces\CollectionComponent
     */
    protected function validateComponent($component)
    {
        if (! $component instanceof Interfaces\CollectionComponent) {
            $component = new static($component);
        }

        return $component;
    }
}
