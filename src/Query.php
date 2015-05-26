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
use League\Url\Interfaces;

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
     * a new instance
     *
     * @param string $data
     */
    public function __construct($data = null)
    {
        if (! is_null($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * sanitize the submitted data
     *
     * @param string $str
     *
     * @return array
     */
    protected function validate($str)
    {
        if (is_bool($str)) {
            throw new InvalidArgumentException('Data passed must be a valid string; received a boolean');
        }

        parse_str($this->validateString($str), $arr);

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function withValue($value)
    {
        if ($value == $this->__toString()) {
            return $this;
        }

        return new static($value);
    }

    /**
     * return a new Query instance from an Array or a traversable object
     *
     * @param  \Traversable|array $data
     *
     * @throws \InvalidArgumentException If $data is invalid
     *
     * @return static
     */
    public static function createFromArray($data)
    {
        return new static(http_build_query(static::validateIterator($data), '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * {@inheritdoc}
     */
    public function format($separator, $enc_type)
    {
        return preg_replace(
            [",=".preg_quote($separator, ',').",", ",=$,"],
            [$separator, ''],
            http_build_query(
                $this->data,
                null,
                $separator,
                $enc_type
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->format('&', PHP_QUERY_RFC3986);
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
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($offset, $default = null)
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
        if (! $query instanceof Interfaces\Query) {
            $query = static::createFromArray($query);
        }

        if ($this->sameValueAs($query)) {
            return $this;
        }

        return static::createFromArray(array_merge($this->data, $query->toArray()));
    }
}
