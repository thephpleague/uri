<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.url
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Modifiers\Filters;

/**
 * Trait to list the Key to remove from a League Uri Collection object
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait Keys
{
    /**
     * The list of keys to remove
     *
     * @var array
     */
    protected $keys;

    /**
     * Return a new instance with a new set of keys
     *
     * @param array $keys the list of keys to remove from the collection
     *
     * @return self
     */
    public function withKeys(array $keys)
    {
        $clone = clone $this;
        $clone->keys = $keys;

        return $clone;
    }
}
