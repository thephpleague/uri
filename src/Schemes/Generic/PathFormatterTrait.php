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
     * @param bool   $has_authority_part does the URI as an authority part
     *
     * @return string
     */
    protected function formatPath($path, $has_authority_part = false)
    {
        if (!$has_authority_part) {
            return preg_replace(',^/+,', '/', $path);
        }

        if (!empty($path) && '/' != $path[0]) {
            return '/'.$path;
        }

        return $path;
    }
}
