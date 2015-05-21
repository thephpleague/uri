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
namespace League\Url\Utilities;

/**
 * A trait with common methods for composed objects
 *
 * @package League.url
 * @since 4.0.0
 */
trait CompositionTrait
{
    /**
     * Returns an instance with the modified component
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param string $name  the component to set
     * @param string $value the component value
     *
     * @return static
     */
    protected function withComponent($name, $value)
    {
        $value = $this->$name->withValue($value);
        if ($this->$name->sameValueAs($value)) {
            return $this;
        }
        $clone = clone $this;
        $clone->$name = $value;

        return $clone;
    }
}
