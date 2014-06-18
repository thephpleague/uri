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

/**
 *  A class to manipulate URL Pass component
 *
 *  @package League.url
 */
class Pass extends AbstractComponent
{

    /**
     * Exchange the object for another one
     *
     * @param Pass $component The object to exchange property with the current object
     *
     * @return void
     */
    public function exchange(Pass $component)
    {
        $this->data = $component->data;
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
