<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.2.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\HierarchicalComponent;
use League\Uri\Types\ImmutableCollectionTrait;
use League\Uri\Types\ImmutableComponentTrait;

/**
 * An abstract class to ease collection like Component object manipulation
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class AbstractHierarchicalComponent implements HierarchicalComponent
{
    use ImmutableCollectionTrait;

    use ImmutableComponentTrait;

    const IS_ABSOLUTE = 1;

    const IS_RELATIVE = 0;

    /**
     * Hierarchical component separator
     *
     * @var string
     */
    protected static $separator;

    /**
     * Is the object considered absolute
     *
     * @var int
     */
    protected $isAbsolute = self::IS_RELATIVE;

    /**
     * new instance
     *
     * @param null|string $str the component value
     */
    abstract public function __construct($str);

    /**
     * Returns an instance with the specified string
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified data
     *
     * @param string $value
     *
     * @return static
     */
    public function modify($value)
    {
        if ($value == $this->__toString()) {
            return $this;
        }

        return new static($value);
    }

    /**
     * Returns whether or not the component is absolute or not
     *
     * @return bool
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

        return $this->createFromArray($data, $this->isAbsolute);
    }

    /**
     * Returns the component literal value. The return type can be
     * <ul>
     * <li> null: If the component is not defined
     * <li> string: Otherwise
     * </ul>
     *
     * @return string|null
     */
    abstract public function getContent();

    /**
     * Returns the instance string representation; If the
     * instance is not defined an empty string is returned
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getContent();
    }

    /**
     * Returns the instance string representation
     * with its optional URI delimiters
     *
     * @return string
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * Returns an instance with the specified component prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the prepended data
     *
     * @param HierarchicalComponent|string $component the component to prepend
     *
     * @return static
     */
    public function prepend($component)
    {
        return $this->createFromArray(
                $this->validateComponent($component),
                $this->isAbsolute
            )->append($this);
    }

    /**
     * Returns an instance with the specified component appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the appended data
     *
     * @param HierarchicalComponent|string $component the component to append
     *
     * @return static
     */
    abstract public function append($component);

    /**
     * Validate a component as a HierarchicalComponentInterface object
     *
     * @param HierarchicalComponent|string $component
     *
     * @return static
     */
    protected function validateComponent($component)
    {
        if (!$component instanceof HierarchicalComponent) {
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

        return new static(static::formatComponentString($data, $type));
    }

    /**
     * Return a formatted component string according to its type
     *
     * @param \Traversable|string[] $data The segments list
     * @param int                   $type
     *
     * @throws InvalidArgumentException If $data is invalid
     *
     * @return string
     */
    protected static function formatComponentString($data, $type)
    {
        $path = implode(static::$separator, static::validateIterator($data));
        if (self::IS_ABSOLUTE == $type) {
            return static::$separator.$path;
        }

        return $path;
    }

    /**
     * Returns an instance with the modified segment
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the replaced data
     *
     * @param int                          $offset    the label offset to remove and replace by
     *                                                the given component
     * @param HierarchicalComponent|string $component the component added
     *
     * @return static
     */
    public function replace($offset, $component)
    {
        if (!empty($this->data) && !$this->hasKey($offset)) {
            return $this;
        }

        $source = $this->toArray();
        $dest   = $this->validateComponent($component)->toArray();
        if ('' == $dest[count($dest) - 1]) {
            array_pop($dest);
        }

        return $this->newCollectionInstance(
            array_merge(array_slice($source, 0, $offset), $dest, array_slice($source, $offset + 1))
        );
    }
}
