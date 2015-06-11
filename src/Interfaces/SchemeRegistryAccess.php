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
 * An Interface to allow accessing the a SchemeRegistry object
 *
 * @package League.url
 * @since 4.0.0
 */
interface SchemeRegistryAccess
{
    /**
     * Return a SchemeRegistry Object
     *
     * @return SchemeRegistry
     */
    public function getSchemeRegistry();
}
