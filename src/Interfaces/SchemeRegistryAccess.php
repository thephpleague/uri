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
 * An Interface to allow accessing the a SchemeRegistry object
 *
 * @package League.url
 * @since   4.0.0
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
