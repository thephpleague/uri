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
use Traversable;

/**
 * Value object representing a URL query component.
 *
 * @package League.url
 * @since  1.0.0
 */
class Query implements Interfaces\Query
{
    /**
     * Trait for Collection type Component
     */
    use Utilities\CollectionTrait;

    /**
     * Trait for parsing and building query string
     */
    use Utilities\QueryFactory;

    /**
     * a new instance
     *
     * @param string $data
     */
    public function __construct($data = null)
    {
        $this->data = $this->validate($data);
    }

    /**
     * sanitize the submitted data
     *
     * @param string $str
     *
     * @throws InvalidArgumentException If reserved characters are used
     *
     * @return array
     */
    protected function validate($str)
    {
        if (is_null($str)) {
            return [];
        }

        $str = $this->validateString($str);
        if (strpos($str, '#') !== false) {
            throw new InvalidArgumentException('the query string must not contain a URL fragment');
        }

        return static::parse($str, '&', false);
    }

    /**
     * return a new Query instance from an Array or a traversable object
     *
     * @param Traversable|array $data
     *
     * @throws InvalidArgumentException If $data is invalid
     *
     * @return static
     */
    public static function createFromArray($data)
    {
        return new static(static::build(static::validateIterator($data), '&', false));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return static::build($this->data, '&', PHP_QUERY_RFC3986);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $res = $this->__toString();
        if (empty($res)) {
            return $res;
        }

        return '?'.$res;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($offset, $default = null)
    {
        $offset = rawurldecode($offset);
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($query)
    {
        if ($query instanceof Interfaces\Query) {
            return $this->mergeQueryObject($query);
        }

        if ($query instanceof Traversable || is_array($query)) {
            return $this->mergeArray($query);
        }

        return $this->mergeArray($this->validate($query));
    }

    /**
     * Merge a Traversable or an Array to the current object
     *
     * @param Traversable|array $query
     *
     * @return static
     */
    protected function mergeArray($query)
    {
        return $this->mergeQueryObject(static::createFromArray($query));

    }

    /**
     * Merge 2 Interfaces\Query objects
     *
     * @param Interfaces\Query $query
     *
     * @return static
     */
    protected function mergeQueryObject(Interfaces\Query $query)
    {
        if ($this->sameValueAs($query)) {
            return $this;
        }

        return static::createFromArray(array_merge($this->data, $query->toArray()));
    }
}
