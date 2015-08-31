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

use League\Uri\Components\Host;
use League\Uri\Interfaces\Components\Host as HostInterface;

/**
 * Host label trait
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait Label
{
    /**
     * A HostInterface object
     *
     * @var HostInterface
     */
    protected $label;

    /**
     * Return a instance with the specified host
     *
     * @param HostInterface|string $label the data to be used
     *
     * @return $this
     */
    public function withLabel($label)
    {
        $clone = clone $this;
        $clone->label = $clone->filterLabel($label);

        return $clone;
    }

    /**
     * Filter and validate the host string
     *
     * @param HostInterface|string $host the data to validate
     *
     * @return HostInterface
     */
    protected function filterLabel($host)
    {
        if ($host instanceof HostInterface) {
            return $host;
        }

        return new Host($host);
    }
}
