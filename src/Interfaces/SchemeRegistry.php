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
     * @return int[]
     */
    public function getStandardPorts($scheme);

    /**
     * Register a new Scheme or add standard Port to an
     * already registered Scheme
     *
     * @param string $scheme
     * @param int|null $port
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
