<?php

namespace League\Url\Components;

use IteratorAggregate;
use Countable;
use ArrayIterator;
use ArrayAccess;
use InvalidArgumentException;

abstract class AbstractComponent extends Validation implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * container holder
     *
     * @var array
     */
    protected $data = array();

    /**
     * The Constructor
     * @param mixed $data The data to add
     */
    public function __construct($data = null)
    {
        $this->set($data);
    }

    /**
     * Set the container data
     * @param mixed $data The data to add
     */
    public function set($data)
    {
        $this->data = $this->validate($data);
    }

    /**
     * IteratorAggregate Interface method
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Countable Interface method
     *
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;

            return;
        }
        $offset = filter_var($offset, FILTER_VALIDATE_INT, array('min_range' => 0));
        if (false === $offset) {
            throw new InvalidArgumentException('Offset must be an integer');
        }
        $this->data[$offset] = (string) $value;
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * ArrayAccess Interface method
     */
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }
}
