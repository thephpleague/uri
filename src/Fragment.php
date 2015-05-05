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

use League\Url\Interfaces\Component;

/**
* Value object representing a URL fragment component.
*
* @package League.url
* @since 1.0.0
*/
class Fragment extends AbstractComponent implements Component
{
    /**
     * Escaping symbols according to http://tools.ietf.org/html/rfc3986#section-3.5
     *
     * @var array
     */
    protected static $rawSymbols = [
        '/', '?', '-', '.', '_', '~', '!',
        '$', '&', '\'', '(', ')', '*', '+',
        ',', ';', '=', ':', '@',
    ];

    /**
     * Encoded escaping symbols according to http://tools.ietf.org/html/rfc3986#section-3.5
     *
     * @var array
     */
    protected static $encodedSymbols = [];

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = filter_var($data, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);

        return rawurldecode(trim($data));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $data = (string) $this->data;
        if (empty(static::$encodedSymbols)) {
            static::$encodedSymbols = array_map(function ($str) {
                return urlencode($str);
            }, static::$rawSymbols);
        }

        return str_replace(static::$encodedSymbols, static::$rawSymbols, rawurlencode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $data = $this->__toString();
        if (empty($data)) {
            return $data;
        }

        return '#'.$data;
    }
}
