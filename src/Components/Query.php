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
use League\Url\Interfaces\EncodingInterface;

/**
 *  A class to manipulate URL Query component
 *
 *  @package League.url
 */
class Query extends AbstractArray implements QueryInterface, EncodingInterface
{
    /**
     * Query encoding type
     *
     * @var integer
     */
    protected $encoding_type = PHP_QUERY_RFC1738;

    /**
     * Possible encoding type list
     *
     * @var array
     */
    protected $encoding_list = array(
        PHP_QUERY_RFC3986 => 1,
        PHP_QUERY_RFC1738 => 1
    );

    /**
     * The Constructor
     *
     * @param mixed   $data     can be string, array or Traversable
     *                          object convertible into Query String
     * @param integer $enc_type specify the RFC to follow when converting
     *                          the data to string
     */
    public function __construct($data = null, $enc_type = PHP_QUERY_RFC1738)
    {
        $this->setEncoding($enc_type);
        $this->set($data);
    }

    /**
     * {@inheritdoc}
     */
    public function setEncoding($enc_type)
    {
        if (! isset($this->encoding_list[$enc_type])) {
            throw new InvalidArgumentException('Invalid value for the encoding type');
        }
        $this->encoding_type = $enc_type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncoding()
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
     * @param array   $arr      the array to encode as a query string
     * @param integer $enc_type the encoding RFC followed
     *
     * @return string
     */
    protected function encode(array $arr, $enc_type)
    {
        if (5 == PHP_MAJOR_VERSION && 4 > PHP_MINOR_VERSION) {
            return $this->encodePHP53($arr, $enc_type);
        }

        return http_build_query($arr, '', '&', $enc_type);
    }

    /**
     * Url encode the query string for PHP5.3
     *
     * @param array   $arr      the array to encode as a query string
     * @param integer $enc_type the encoding RFC followed
     *
     * @return string
     */
    protected function encodePHP53(array $arr, $enc_type)
    {
        $query = http_build_query($arr, '', '&');
        if (PHP_QUERY_RFC1738 == $enc_type) {
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
