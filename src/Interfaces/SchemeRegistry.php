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
namespace League\Url\Interfaces;

use Countable;
use IteratorAggregate;

/**
 * An Interface to manage Scheme registration
 *
 * @package League.url
 * @since 4.0.0
 */
interface SchemeRegistry extends Countable, IteratorAggregate
{
    /**
     * Tell whether a scheme is present in the registry
     *
     * @param string $scheme
     *
     * @return bool
     */
    public function has($scheme);

    /**
     * Return the ports associated to the scheme
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the submitted scheme is unknown to the registry
     *
     * @return Port[]
     */
    public function getStandardPorts($scheme);

    /**
     * Tell whether a scheme uses the specified port as
     * its standard port
     *
     * @param  string       $scheme
     * @param  Port|int|nul $port
     *
     * @throws InvalidArgumentException If the submitted scheme is unknown to the registry
     *
     * @return bool
     */
    public function isStandardPort($scheme, $port);

    /**
     * Register a new scheme or add a new standard Port to an
     * already registered scheme
     *
     * @param  string       $scheme
     * @param  Port|int|nul $port
     *
     * @throws InvalidArgumentException If the submitted scheme is invalid
     * @throws InvalidArgumentException If the submitted port is invalid
     */
    public function add($scheme, $port = null);

    /**
     * Remove a scheme from the Registry
     *
     * @param string $scheme
     */
    public function remove($scheme);
}
