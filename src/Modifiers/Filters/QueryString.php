<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Modifiers\Filters;

use League\Uri\Components\Query;
use League\Uri\Interfaces\Components\Query as QueryInterface;

/**
 * Query string modifier
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait QueryString
{
    /**
     * A Query object
     *
     * @var QueryInterface
     */
    protected $query;

    /**
     * Return a instance with the specified query string
     *
     *
     * @param QueryInterface|string $query the data to be merged query can be
     *                                     - another Interfaces\Query object
     *                                     - a string or a Stringable object
     *
     * @return $this
     */
    public function withQuery($query)
    {
        $clone = clone $this;
        $clone->query = $clone->filterQuery($query);

        return $clone;
    }

    /**
     * Filter and validate the query data
     *
     * @param QueryInterface|string $query the data to be merged query can be
     *                                     - another Interfaces\Query object
     *                                     - a string or a Stringable object
     *
     * @return QueryInterface
     */
    protected function filterQuery($query)
    {
        if ($query instanceof QueryInterface) {
            return $query;
        }

        return new Query($query);
    }
}
