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
use League\Url\Interfaces\QueryInterface;

/**
 *  A class to manipulate URL Query component
 *
 *  @package League.url
 */
class Query extends AbstractArray implements QueryInterface
{
    /**
     * Query encoding type
     *
     * @var integer
     */
    protected $encoding_type = self::PHP_QUERY_RFC1738;

    /**
     * Possible encoding type list
     *
     * @var array
     */
    protected $encoding_list = array(
        self::PHP_QUERY_RFC3986 => 1,
        self::PHP_QUERY_RFC1738 => 1
    );

    /**
     * The Constructor
     *
     * @param mixed $data can be string, array or Traversable
     *                               object convertible into Query String
     * @param integer $encoding_type specify the RFC to follow when using __toString
     */
    public function __construct($data = null, $encoding_type = self::PHP_QUERY_RFC1738)
    {
        $this->setEncodingType($encoding_type);
        $this->set($data);
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodingType($enc_type)
    {
        if (! isset($this->encoding_list[$enc_type])) {
            throw new InvalidArgumentException('Invalid value for the encoding type');
        }
        $this->encoding_type = $enc_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncodingType()
    {
        return $this->encoding_type;
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        $this->data = array_filter($this->validate($data), function ($value) {
            $value = trim($value);

            return null !== $value && '' !== $value;
        });
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
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $value = $this->__toString();
        if ('' != $value) {
            $value = '?'.$value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($data)
    {
        $this->set(array_merge($this->data, $this->validate($data)));
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        return $this->convertToArray($data, function ($str) {
            if ('' == $str) {
                return array();
            }
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
        $query = http_build_query($str, '', '&');
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
        $this->modify(array($offset => $value));
    }
}
