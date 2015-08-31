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

use InvalidArgumentException;
use League\Uri\Interfaces\Components\Collection;

/**
 * Flag trait to Filter League\Uri Collections
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait Flag
{
    /**
     * A HostInterface object
     *
     * @var HostInterface
     */
    protected $flag;

    /**
     * Available flags
     *
     * @var array
     */
    protected static $flagList = [
        Collection::FILTER_USE_VALUE => 1,
        Collection::FILTER_USE_KEY => 2,
    ];

    /**
     * Return a instance with the specified host
     *
     * @param HostInterface|string $label the data to be used
     *
     * @throws InvalidArgumentException for invalid host
     *
     * @return $this
     */
    public function withFlag($flag)
    {
        $clone = clone $this;
        $clone->flag = $clone->filterFlag($flag);

        return $clone;
    }

    /**
     * Filter and validate the host string
     *
     * @param HostInterface|string $host the data to validate
     *
     * @throws \InvalidArgumentException for invalid query strings.
     *
     * @return HostInterface
     */
    protected function filterFlag($flag)
    {
        if (isset(static::$flagList[$flag])) {
            return $flag;
        }

        throw new InvalidArgumentException('Invalid Flag');
    }
}
