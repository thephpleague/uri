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

use ArrayAccess;
use RuntimeException;

/**
 *  A class to manipulate URL Query component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Query extends AbstractArray implements QueryInterface, ArrayAccess
{
    /**
     * The Constructor
     *
     * @param mixed $data can be string, array or Traversable
     *                    object convertible into Query String
     */
    public function __construct($data = null)
    {
        $this->set($data);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        $this->data = array_filter($this->validate($data), function ($value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            return null !== $value && '' !== $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (!$this->data) {
            return null;
        }

        return str_replace(
            array('%E7', '+'),
            array('~', '%20'),
            http_build_query($this->data, '', '&')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(ComponentInterface $component)
    {
        return $this->__toString() == $component->__toString();
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

            //let's preserve the key params
            $str = preg_replace_callback('/(?:^|(?<=&))[^=[]+/', function ($match) {
                return bin2hex(urldecode($match[0]));
            }, $str);
            parse_str($str, $arr);

            //hexbin does not work in PHP 5.3
            $arr = array_combine(array_map(function ($value) {
                return pack('H*', $value);

            }, array_keys($arr)), $arr);

            return $arr;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new RuntimeException('offset can not be null');
        }
        $this->modify(array($offset => $value));
    }
}
