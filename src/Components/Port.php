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

use RuntimeException;

/**
 *  A class to manipulate URL Port component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Port extends AbstractComponent
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

        $data = filter_var($data, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 1),
        ));

        if (! $data) {
            throw new RuntimeException('A port must be a valid positive integer');
        }

        return (int) $data;
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
