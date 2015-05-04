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
namespace League\Url;

use ArrayIterator;
use InvalidArgumentException;
use Traversable;

/**
 * An abstract class to ease SegmentComponent object creation
 *
 * @package  League.url
 * @since  3.0.0
 */
abstract class AbstractSegmentComponent extends AbstractComponent
{
    /**
     * The Component Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * is the SegmentComponent absolute
     *
     * @var boolean
     */
    protected $is_absolute = false;

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return $this->is_absolute;
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
    public function offsets($data = null)
    {
        if (is_null($data)) {
            return array_keys($this->data);
        }

        return array_keys($this->data, (new static($data))->get(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function append(Interfaces\SegmentComponent $component)
    {
        $source = $this->toArray();
        $dest   = $component->toArray();
        if (count($source) && '' == $source[count($source) - 1]) {
            array_pop($source);
        }

        return static::createFromArray(array_merge($source, $dest), $this->is_absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(Interfaces\SegmentComponent $component)
    {
        $source = $this->toArray();
        $dest   = $component->toArray();
        if (count($dest) && '' == $dest[count($dest) - 1]) {
            array_pop($dest);
        }

        return static::createFromArray(array_merge($dest, $source), $this->is_absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(Interfaces\SegmentComponent $component, $offset)
    {
        if (! empty($this->data) && ! $this->hasOffset($offset)) {
            return $this;
        }
        $source = $this->toArray();
        $dest   = $component->toArray();
        if ('' == $dest[count($dest) - 1]) {
            array_pop($dest);
        }
        $dest = array_merge(array_slice($source, 0, $offset), $dest, array_slice($source, $offset+1));

        return static::createFromArray($dest, $this->is_absolute);
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
}
