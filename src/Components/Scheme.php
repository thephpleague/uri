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

use League\Url\UrlConstants;
use RuntimeException;

/**
 *  A class to manipulate URL Scheme component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Scheme extends AbstractComponent
{
    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = parent::validate($data);
        if (is_null($data)) {
            return $data;
        }

        $data = filter_var($data, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => '/^'.UrlConstants::SCHEME_REGEXP.'$/i'),
        ));

        if (! $data) {
            throw new RuntimeException('This class only deals with http URL');
        }

        return strtolower($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $value = $this->__toString();
        if ('' != $value) {
            $value .= '://';
        }

        return $value;
    }
}
