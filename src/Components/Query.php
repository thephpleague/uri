<?php

namespace League\Url\Components;

use Traversable;
use InvalidArgumentException;

class Query extends AbstractComponent implements ComponentInterface
{
    /**
     * encode query string according to RFC 1738
     */
    const PHP_QUERY_RFC1738 = 1;

    /**
     * encode query string according to RFC 3986
     */
    const PHP_QUERY_RFC3986 = 2;

    /**
     * Query encoding type
     * @var array
     */
    protected $encoding_type = self::PHP_QUERY_RFC1738;

    /**
     * The Constructor
     *
     * @param mixed   $data          can be string, array or Traversable
     *                               object convertible into Query String
     * @param integer $encoding_type specify the RFC to follow when using __toString
     */
    public function __construct($data, $encoding_type = self::PHP_QUERY_RFC1738)
    {
        $this->setEncodingType($encoding_type);
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (! $this->data) {
            return '';
        }

        return $this->encode($this->data, $this->encoding_type);
    }

    /**
     * Validate the Query String Encoding Mode
     *
     * @param integer $encoding_type
     *
     * @return integer
     */
    protected function validateEncodingType($encoding_type)
    {
        static $arr = array(self::PHP_QUERY_RFC3986 => 1, self::PHP_QUERY_RFC1738 => 1);
        if (isset($arr[$encoding_type])) {
            return $encoding_type;
        }

        return self::PHP_QUERY_RFC1738;
    }

    /**
     * Set the Query String encoding type (see {@link http_build_query})
     *
     * @param integer $encoding_type
     */
    public function setEncodingType($encoding_type)
    {
        $this->encoding_type = $this->validateEncodingType($encoding_type);
    }

    /**
     * return the current Encoding type value
     *
     * @return integer
     */
    public function getEncodingType()
    {
        return $this->encoding_type;
    }

    /**
     * Validate data before insertion into a URL query component
     *
     * @param mixed $data the data to insert
     *
     * @return array
     *
     * @throws RuntimeException If the data can not be converted to array
     */
    public function validate($data)
    {
        return $this->validateComponent($data, function ($str) {
            if ('?' == $str[0]) {
                $str = substr($str, 1);
            }
            parse_str($str, $arr);

            return $arr;
        });
    }

    /**
     * Url encode the query string
     *
     * @param array   $str           the array to encode as a query string
     * @param integer $encoding_type the encoding RFC followed
     *
     * @return string
     */
    protected function encode(array $str, $encoding_type)
    {
        if (defined('PHP_QUERY_RFC3986')) {
            return http_build_query($str, '', '&', $encoding_type);
        }
        $query = http_build_query($str);
        if (self::PHP_QUERY_RFC3986 != $encoding_type) {
            return $query;
        }

        return str_replace(array('%E7', '+'), array('~', '%20'), $query);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new InvalidArgumentException('offset can not be null');
        }
        $this->data[$offset] = $value;
    }

    public function remove($data)
    {
        if (is_string($data) || (is_object($data) && method_exists($data, '__toString'))) {
            $data = array((string) $data);
        }
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new InvalidArgumentException('your input should be iterable');
        }
        foreach ($data as $offset) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Update the Query String Data
     *
     * @param mixed $data the data to update
     */
    public function modify($data)
    {
        $this->data = array_merge($this->data, $this->validate($data));
    }

}
