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

/**
 * Value object representing a URL Scheme registration system.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since 4.0.0
 */
interface SchemeRegistry extends Collection
{
    /**
     * Return the ports associated to the scheme
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the submitted scheme is unknown to the registry
     *
     * @return Port
     */
    public function getPort($scheme);

    /**
     * Returns an instance merge with the specified registry data
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified registry data
     *
     * @param static|\Traversable|array $query the data to be merged can be
     *                                         - another Interfaces\SchemeRegistry object
     *                                         - a Traversable object
     *                                         - an array
     *
     * @return static
     */
    public function merge($registry);
}
