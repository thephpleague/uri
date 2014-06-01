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

class Port extends Component
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

        return filter_var($data, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 1, 'default' => null)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $value = $this->__toString();
        if ('' != $value) {
            $value = ':'.$value;
        }

        return $value;
    }
}
