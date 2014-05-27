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

use RuntimeException;

class Scheme extends Component
{
    /**
     * {@inheritdoc}
     */
    public function validate($data)
    {
        $data = parent::validate($data);
        if (is_null($data)) {
            return $data;
        }

        $data = filter_var($data, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => '/^http(s?)$/i')
        ));

        if (! $data) {
            throw new RuntimeException('This class only deals with http URL');
        }

        return $data;
    }
}
