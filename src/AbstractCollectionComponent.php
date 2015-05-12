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
 * An abstract class to ease collection like Component object manipulation
 *
 * @package  League.url
 * @since  3.0.0
 */
abstract class AbstractCollectionComponent implements Interfaces\CollectionComponent
{
    /**
     * Trait for Collection type Component
     */
    use Util\CollectionTrait;

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
    public function append($component)
    {
        $source = $this->toArray();
        $dest   = static::validateComponent($component)->toArray();
        if (count($source) && '' == $source[count($source) - 1]) {
            array_pop($source);
        }

        return static::createFromArray(array_merge($source, $dest), $this->is_absolute);
    }

    /**
     * return a new CollectionComponent instance from an Array or a traversable object
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
    public function prepend($component)
    {
        return static::createFromArray(static::validateComponent($component), $this->is_absolute)->append($this);
    }

    /**
     * {@inheritdoc}
     */
    public function replace($component, $offset)
    {
        if (! empty($this->data) && ! $this->hasOffset($offset)) {
            return $this;
        }

        $source = $this->toArray();
        $dest   = static::validateComponent($component)->toArray();
        if ('' == $dest[count($dest) - 1]) {
            array_pop($dest);
        }
        $dest = array_merge(array_slice($source, 0, $offset), $dest, array_slice($source, $offset+1));

        return static::createFromArray($dest, $this->is_absolute);
    }
}
