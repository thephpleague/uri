<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.2.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Components;

use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 *  A class to manipulate URL Array like components
 *
 *  @package League.url
 *  @since  3.0.0
 */
abstract class AbstractArray implements IteratorAggregate, Countable
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
     * @param int $mode
     *
     * @return integer
     */
    public function count($mode = COUNT_NORMAL)
    {
        return count($this->data, $mode);
    }

    /**
     * ArrayAccess Interface method
     *
     * @param int|string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * ArrayAccess Interface method
     *
     * @param int|string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * ArrayAccess Interface method
     *
     * @param int|string $offset
     *
     * @return null
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
     *                           a given string into the corresponding component
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
