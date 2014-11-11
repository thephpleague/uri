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

/**
 *  A class to manipulate URL Fragment component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Fragment extends AbstractComponent
{
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $value = parent::__toString();

        // according to http://tools.ietf.org/html/rfc3986#section-3.5
        $rawSymbols = array(
            '/', '?', '-', '.', '_', '~', '!',
            '$', '&', '\'', '(', ')', '*', '+',
            ',', ';', '=', ':', '@',
        );

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
