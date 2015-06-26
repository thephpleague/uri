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
namespace League\Url;

use InvalidArgumentException;

/**
 * An abstract class to ease collection like Component object manipulation
 *
 * @package League.url
 * @since   4.0.0
 */
abstract class AbstractHierarchicalComponent implements Interfaces\HierarchicalComponent
{
    const IS_ABSOLUTE = 1;
    const IS_RELATIVE = 0;

    /**
     * Is the HierarchicalComponent absolute
     *
     * @var int
     */
    protected $is_absolute = self::IS_RELATIVE;

    /**
     * Trait for ComponentTrait method
     */
    use Utilities\ComponentTrait;

    /**
     * Trait for Collection type Component
     */
    use Utilities\CollectionTrait;

    /**
     * New Instance
     *
     * @param string $str the component string representation
     */
    public function __construct($str = null)
    {
        $this->init($str);
    }

    /**
     * Initialize the object data
     *
     * @param string $str the raw component string
     */
    abstract protected function init($str);

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return $this->is_absolute == self::IS_ABSOLUTE;
    }

    /**
     * Return a new instance when needed
     *
     * @param array $data
     *
     * @return static
     */
    protected function newCollectionInstance(array $data)
    {
        if ($data == $this->data) {
            return $this;
        }

        return static::createFromArray($data, $this->is_absolute);
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
    public function prepend($component)
    {
        return static::createFromArray(static::validateComponent($component), $this->is_absolute)->append($this);
    }

    /**
     * {@inheritdoc}
     */
    public function append($component)
    {
        $source = $this->toArray();
        if (count($source) && '' == $source[count($source) - 1]) {
            array_pop($source);
        }

        return $this->newCollectionInstance(array_merge($source, static::validateComponent($component)->toArray()));
    }

    /**
     * Validate a component as a Interfaces\HierarchicalComponent object
     *
     * @param mixed $component
     *
     * @throws InvalidArgumentException if the value can not be converted
     *
     * @return static
     */
    protected function validateComponent($component)
    {
        if (!$component instanceof Interfaces\HierarchicalComponent) {
            return $this->modify($component);
        }

        return $component;
    }

    /**
     * return a new instance from an array or a traversable object
     *
     * @param \Traversable|string[] $data  The segments list
     * @param int                   $type  One of the constant IS_ABSOLUTE or IS_RELATIVE
     *
     * @throws InvalidArgumentException If $data is invalid
     * @throws InvalidArgumentException If $is_absolute is not a recognized constant
     *
     * @return static
     */
    public static function createFromArray($data, $type = self::IS_RELATIVE)
    {
        static $type_list = [self::IS_ABSOLUTE => 1, self::IS_RELATIVE => 1];

        if (!isset($type_list[$type])) {
            throw new InvalidArgumentException('Please verify the submitted constant');
        }
        $component = implode(static::$delimiter, static::validateIterator($data));

        return new static(static::formatComponentString($component, $type));
    }

    /**
     * Return a formatted component string according to its type
     *
     * @param string $str
     * @param int    $type
     *
     * @return string
     */
    protected static function formatComponentString($str, $type)
    {
        if (self::IS_ABSOLUTE == $type) {
            return static::$delimiter.$str;
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function replace($offset, $component)
    {
        if (!empty($this->data) && !$this->hasOffset($offset)) {
            return $this;
        }

        $source = $this->toArray();
        $dest   = static::validateComponent($component)->toArray();
        if ('' == $dest[count($dest) - 1]) {
            array_pop($dest);
        }

        return $this->newCollectionInstance(
            array_merge(array_slice($source, 0, $offset), $dest, array_slice($source, $offset+1))
        );
    }
}
