<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Url\Utilities;

/**
 * A trait to set and get immutable value
 *
 * @package League.url
 * @since   4.0.0
 */
trait ImmutableProperty
{
    /**
     * Returns an instance with the modified component
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param string $property the property to set
     * @param string $value    the property value
     *
     * @return static
     */
    protected function withProperty($property, $value)
    {
        $value = $this->$property->modify($value);
        if ($this->$property->sameValueAs($value)) {
            return $this;
        }
        $newInstance = clone $this;
        $newInstance->$property = $value;

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
