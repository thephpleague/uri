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
trait ImmutableValue
{
    /**
     * Perfom cleanup operation
     */
    abstract protected function init();

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
    protected function withProperty($name, $value)
    {
        $value = $this->$name->withValue($value);
        if ($this->$name->sameValueAs($value)) {
            return $this;
        }
        $newInstance = clone $this;
        $newInstance->$name = $value;
        $newInstance->init();

        return $newInstance;
    }

    /**
     * Magic read-only for all Part/Component URL properties
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
