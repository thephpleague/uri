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

use InvalidArgumentException;

/**
 * Hierarchical Component index trait
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait Offset
{
    /**
     * Offset key to replace the content
     *
     * @var int
     */
    protected $offset;

    /**
     * Return a new modifier with a new offset key
     *
     * @param int $offset
     *
     * @return $this
     */
    public function withOffset($offset)
    {
        $clone = clone $this;
        $clone->offset = $this->filterOffset($offset);

        return $clone;
    }

    /**
     * Filter and validate the offset key
     *
     * @param int $offset
     *
     * @throws InvalidArgumentException if the Offset key is invalid
     *
     * @return int
     */
    protected function filterOffset($offset)
    {
        $offset = filter_var($offset, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if (false === $offset) {
            throw new InvalidArgumentException('The submitted index is invalid');
        }

        return $offset;
    }
}
