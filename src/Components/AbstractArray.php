<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Components;

use Closure;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use ArrayAccess;
use RuntimeException;
use Traversable;

/**
 *  A class to manipulate URL Segment like components
 *
 *  @package League.url
 */
abstract class AbstractArray implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * container holder
     *
     * @var array
     */
    protected $data = array();

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
    public function keys()
    {
        $args = func_get_args();
        if (! $args) {
            return array_keys($this->data);
        }

        return array_keys($this->data, $args[0], true);
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
    abstract public function offsetSet($offset, $value);

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

    public static function isStringable($data)
    {
        return is_string($data) || (is_object($data)) && (method_exists($data, '__toString'));
    }

    /**
     * convert a given data into an array
     *
     * @param mixed    $data     the data to insert
     * @param \Closure $callback a callable function to be called to parse
     *                           a given string into the corrseponding component
     *
     * @return array
     *
     * @throws \RuntimeException if the data is not valid
     */
    protected function convertToArray($data, Closure $callback)
    {
        if (is_null($data)) {
            return array();
        } elseif ($data instanceof Traversable) {
            return iterator_to_array($data);
        } elseif (self::isStringable($data)) {
            $data = (string) $data;
            $data = trim($data);
            $data = $callback($data);
        }

        if (! is_array($data)) {
            throw new RuntimeException('Your submitted data could not be converted into a proper array');
        }

        return $data;
    }
}
