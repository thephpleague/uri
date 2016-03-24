<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.1
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Query as QueryInterface;
use League\Uri\QueryParser;
use League\Uri\Types\ImmutableCollectionTrait;
use League\Uri\Types\ImmutableComponentTrait;

/**
 * Value object representing a URI query component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Query implements QueryInterface
{
    use ImmutableComponentTrait;

    use ImmutableCollectionTrait;

    /**
     * Key/pair separator character
     *
     * @var string
     */
    protected static $separator = '&';

    /**
     * a new instance
     *
     * @param string $data
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->data = $this->validate($data);
        }
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
        $str = $this->validateString($str);
        if (strpos($str, '#') !== false) {
            throw new InvalidArgumentException('the query string must not contain a URI fragment');
        }

        return (new QueryParser())->parse($str, static::$separator, false);
    }

    /**
     * return a new Query instance from an Array or a traversable object
     *
     * @param \Traversable|array $data
     *
     * @return static
     */
    public static function createFromArray($data)
    {
        $query = (new QueryParser())->build(
            static::validateIterator($data),
            static::$separator,
            PHP_QUERY_RFC3986
        );

        return new static($query);
    }

    /**
     * Returns the instance string representation; If the
     * instance is not defined an empty string is returned
     *
     * @return string
     */
    public function __toString()
    {
        return (new QueryParser())->build($this->data, static::$separator, PHP_QUERY_RFC3986);
    }

    /**
     * Returns the instance string representation
     * with its optional URI delimiters
     *
     * @return string
     */
    public function getUriComponent()
    {
        $query = $this->__toString();
        if ('' !== $query) {
            $query = QueryInterface::DELIMITER.$query;
        }

        return $query;
    }

    /**
     * Retrieves a single query parameter.
     *
     * Retrieves a single query parameter. If the parameter has not been set,
     * returns the default value provided.
     *
     * @param string $offset  the parameter name
     * @param mixed  $default Default value to return if the parameter does not exist.
     *
     * @return mixed
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
     * Returns an instance merge with the specified query
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified query
     *
     * @param Query|string $query the data to be merged query can be
     *                            - another Interfaces\Query object
     *                            - a string or a Stringable object
     *
     * @return static
     */
    public function merge($query)
    {
        if (!$query instanceof QueryInterface) {
            $query = static::createFromArray($this->validate($query));
        }

        if ($this->sameValueAs($query)) {
            return $this;
        }

        return static::createFromArray(array_merge($this->toArray(), $query->toArray()));
    }

    /**
     * Sort the query string by offset, maintaining offset to data correlations.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified query
     *
     * @param callable|int $sort a PHP sort flag constant or a comparaison function
     *                           which must return an integer less than, equal to,
     *                           or greater than zero if the first argument is
     *                           considered to be respectively less than, equal to,
     *                           or greater than the second.
     *
     * @return static
     */
    public function ksort($sort = SORT_REGULAR)
    {
        $func = is_callable($sort) ? 'uksort' : 'ksort';
        $data = $this->data;
        $func($data, $sort);
        if ($data === $this->data) {
            return $this;
        }

        return static::createFromArray($data);
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

        return static::createFromArray($data);
    }
}
