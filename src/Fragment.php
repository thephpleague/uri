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

use League\Url\Interfaces\ComponentInterface;

/**
 *  A class to manipulate URL Fragment component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Fragment extends AbstractComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $value = str_replace(null, '', $this->get());

        // according to http://tools.ietf.org/html/rfc3986#section-3.5
        $rawSymbols = [
            '/', '?', '-', '.', '_', '~', '!',
            '$', '&', '\'', '(', ')', '*', '+',
            ',', ';', '=', ':', '@',
        ];

        $encodedSymbols = array_map(function ($symbol) {
            return urlencode($symbol);
        }, $rawSymbols);

        return str_replace(
            $encodedSymbols,
            $rawSymbols,
            rawurlencode($value)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $value = $this->__toString();
        if ('' != $value) {
            $value = '#'.$value;
        }

        return $value;
    }
}
