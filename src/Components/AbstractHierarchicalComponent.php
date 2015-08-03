<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces;
use League\Uri\Types;

/**
 * An abstract class to ease collection like Component object manipulation
 *
 * @package League.uri
 * @since   4.0.0
 */
abstract class AbstractHierarchicalComponent implements Interfaces\Components\HierarchicalComponent
{
    const IS_ABSOLUTE = 1;
    const IS_RELATIVE = 0;

    /**
     * Is the HierarchicalComponent absolute
     *
     * @var int
     */
    protected $isAbsolute = self::IS_RELATIVE;

    /*
     * common immutable value object methods
     */
    use Types\ImmutableComponentTrait;

    /*
     * immutable collection methods
     */
    use Types\ImmutableCollectionTrait;

    /**
     * New Instance
     *
     * @param string $str the component string representation
     */
    public function __construct($str = null)
    {
        if (null !== $str) {
            $this->init($str);
        }
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
        return $this->isAbsolute == self::IS_ABSOLUTE;
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

        return static::createFromArray($data, $this->isAbsolute);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->isNull() ? '' : $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($component)
    {
        return static::createFromArray(static::validateComponent($component), $this->isAbsolute)->append($this);
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
     * Validate a component as a Interfaces\Components\HierarchicalComponent object
     *
     * @param mixed $component
     *
     * @throws InvalidArgumentException if the value can not be converted
     *
     * @return static
     */
    protected function validateComponent($component)
    {
        if (!$component instanceof Interfaces\Components\HierarchicalComponent) {
            return $this->modify($component);
        }

        return $component;
    }

    /**
     * return a new instance from an array or a traversable object
     *
     * @param \Traversable|string[] $data The segments list
     * @param int                   $type one of the constant IS_ABSOLUTE or IS_RELATIVE
     *
     * @throws InvalidArgumentException If $data is invalid
     * @throws InvalidArgumentException If $type is not a recognized constant
     *
     * @return static
     */
    public static function createFromArray($data, $type = self::IS_RELATIVE)
    {
        static $type_list = [self::IS_ABSOLUTE => 1, self::IS_RELATIVE => 1];

        if (!isset($type_list[$type])) {
            throw new InvalidArgumentException('Please verify the submitted constant');
        }
        $component = implode(static::$separator, static::validateIterator($data));

        return new static(static::formatComponentString($component, $type));
    }

    /**
     * Return a formatted component string according to its type
     *
     * @param null|string $str
     * @param int         $type
     *
     * @return string
     */
    protected static function formatComponentString($str, $type)
    {
        if (null !== $str && self::IS_ABSOLUTE == $type) {
            return static::$separator.$str;
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function replace($key, $component)
    {
        if (!empty($this->data) && !$this->hasKey($key)) {
            return $this;
        }

        $source = $this->toArray();
        $dest   = static::validateComponent($component)->toArray();
        if ('' == $dest[count($dest) - 1]) {
            array_pop($dest);
        }

        return $this->newCollectionInstance(
            array_merge(array_slice($source, 0, $key), $dest, array_slice($source, $key + 1))
        );
    }
}
