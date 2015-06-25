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
namespace League\Url\Interfaces;

/**
 * Value object representing an URL hierarchical component.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since   4.0.0
 */
interface HierarchicalComponent extends Collection, Component
{
    /**
     * Returns an instance with the specified component appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the appended data
     *
     * @param HierarchicalComponent|string $component the component to append
     *
     * @return static
     */
    public function append($component);

    /**
     * Returns an instance with the specified component prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the prepended data
     *
     * @param HierarchicalComponent|string $component the component to prepend
     *
     * @return static
     */
    public function prepend($component);

    /**
     * Returns an instance with the modified segment
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component with the replaced data
     *
     * @param int                          $offset    the label offset to remove and replace by
     *                                                the given component
     * @param HierarchicalComponent|string $component the component added
     *
     * @return static
     */
    public function replace($offset, $component);
}
