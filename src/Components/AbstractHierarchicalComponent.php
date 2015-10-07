<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.url
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * @inheritdoc
     */
    public function prepend($component)
    {
        return $this->createFromArray(
            $this->validateComponent($component),
            $this->isAbsolute)
        ->append($this);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function replace($key, $component)
    {
        if (!empty($this->data) && !$this->hasKey($key)) {
            return $this;
        }

        $source = $this->toArray();
        $dest   = $this->validateComponent($component)->toArray();
        if ('' == $dest[count($dest) - 1]) {
            array_pop($dest);
        }

        return $this->newCollectionInstance(
            array_merge(array_slice($source, 0, $key), $dest, array_slice($source, $key + 1))
        );
    }
}
