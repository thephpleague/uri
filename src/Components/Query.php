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

use Traversable;
use InvalidArgumentException;
use RuntimeException;
use League\Url\Interfaces\QueryInterface;

class Query extends AbstractSegment implements QueryInterface
{
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
    public function get()
    {
        if (! $this->data) {
            return null;
        }

        return $this->encode($this->data, $this->encoding_type);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return str_replace(null, '', $this->get());
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
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new InvalidArgumentException('offset can not be null');
        }
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($data)
    {
        $this->data = array_merge($this->data, $this->validate($data));
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data)
    {
        throw new RuntimeException('This method is not supported');
    }
}
