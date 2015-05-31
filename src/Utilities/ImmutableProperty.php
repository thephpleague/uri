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
 * A trait to set and get immutable value
 *
 * @package League.url
 * @since 4.0.0
 */
trait ImmutableProperty
{
    /**
     * Perfom cleanup operation
     */
    abstract protected function cleanUp();

    /**
     * Returns an instance with the modified component
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param string $property  the property to set
     * @param string $value     the property value
     *
     * @return static
     */
    protected function withProperty($property, $value)
    {
        $value = $this->$property->withValue($value);
        if ($this->$property->sameValueAs($value)) {
            return $this;
        }
        $newInstance = clone $this;
        $newInstance->$property = $value;
        $newInstance->cleanUp();

        return $newInstance;
    }

    /**
     * Magic read-only for protected properties
     *
     * @param string $property The property to read from
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }
}
