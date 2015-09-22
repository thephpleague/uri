<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Query as QueryInterface;
use League\Uri\QueryParser;
use League\Uri\Types\ImmutableCollectionTrait;
use League\Uri\Types\ImmutableComponentTrait;
use Traversable;

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
     * @param Traversable|array $data
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
     * @inheritdoc
     */
    public function __toString()
    {
        return (new QueryParser())->build($this->data, static::$separator, PHP_QUERY_RFC3986);
    }

    /**
     * @inheritdoc
     */
    public function getUriComponent()
    {
        $query = $this->__toString();
        if (!empty($query)) {
            $query = QueryInterface::DELIMITER.$query;
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function getValue($key, $default = null)
    {
        $key = rawurldecode($key);
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * @inheritdoc
     */
    public function merge($query)
    {
        if ($query instanceof QueryInterface) {
            return $this->mergeQuery($query);
        }

        return $this->mergeQuery(static::createFromArray($this->validate($query)));
    }

    /**
     * Merge two QueryInterface objects
     *
     * @param QueryInterface $query
     *
     * @return static
     */
    protected function mergeQuery(QueryInterface $query)
    {
        if ($this->sameValueAs($query)) {
            return $this;
        }

        return static::createFromArray(array_merge($this->data, $query->toArray()));
    }

    /**
     * @inheritdoc
     */
    public function ksort($sort = SORT_REGULAR)
    {
        $func = 'ksort';
        if (is_callable($sort)) {
            $func = 'uksort';
        }
        $data = $this->data;
        $func($data, $sort);
        if ($data === $this->data) {
            return $this;
        }

        return static::createFromArray($data);
    }
}
