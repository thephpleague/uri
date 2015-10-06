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
namespace League\Uri\Schemes\Generic;

/**
 * A trait to format the Path component
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait PathFormatterTrait
{
    /**
     * Format the Path in a URI string
     *
     * @param string $path
     * @param string $authority the Authority part
     *
     * @return string
     */
    protected function formatPath($path, $authority)
    {
        if ('' == $authority) {
            return preg_replace(',^/+,', '/', $path);
        }

        if (!empty($path) && '/' != $path[0]) {
            return '/'.$path;
        }

        return $path;
    }
}
