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

use InvalidArgumentException;
use League\Url\Interfaces;
use League\Url\Util;

/**
 * An abstract class to ease SegmentComponent object manipulation
 *
 * @package  League.url
 * @since  3.0.0
 */
abstract class AbstractSegmentComponent implements Interfaces\SegmentComponent
{
    /**
     * Trait for Collection type Component
     */
    use Util\CollectionComponent;

    /**
     * New Instance of Path
     *
     * @param string $str the path
     */
    public function __construct($str = null)
    {
        $this->init($str);
    }

    /**
     * Initiliaze the object data
     *
     * @param string $str the raw component string
     */
    abstract protected function init($str);

    /**
     * {@inheritdoc}
     */
    public function withValue($value)
    {
        return new static($value);
    }

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
    public function getUriComponent()
    {
        return $this->__toString();
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
     * return a new SegmentComponent instance from an Array or a traversable object
     *
     * @param \Traversable|array $data
     * @param bool               $is_absolute
     *
     * @throws \InvalidArgumentException If $data is invalid
     *
     * @return static
     */
    public static function createFromArray($data, $is_absolute = false)
    {
        $component = implode(static::$delimiter, static::validateIterator($data));
        if ($is_absolute) {
            $component = static::$delimiter.$component;
        }

        return new static($component);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(Interfaces\SegmentComponent $component)
    {
        return static::createFromArray($component, $this->is_absolute)->append($this);
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
}
