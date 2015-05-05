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
namespace League\Url\Util;

use InvalidArgumentException;
use League\Url\Interfaces\Component;

/**
 * A trait with common method for Component
 *
 * @package League.url
 * @since 4.0.0
 */
trait ComponentTrait
{
    /**
     * {@inheritdoc}
     */
    public abstract function getUriComponent();

    /**
     * validate a string
     *
     * @param  mixed $str
     *
     * @throws \InvalidArgumentException if the submitted data can not be converted to string
     *
     * @return string
     */
    protected function validateString($str)
    {
        if (is_null($str) || is_scalar($str) || (is_object($str) && method_exists($str, '__toString'))) {
            return trim($str);
        }

        throw new InvalidArgumentException('Data passed must be a valid string or convertible into a string');
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Component $component)
    {
        return $component->getUriComponent() == $this->getUriComponent();
    }
}
