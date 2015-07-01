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
namespace League\Uri\Url;

use League\Uri\Interfaces\Path;

/**
 * A trait to format the Path component
 *
 * @package League.url
 * @since   4.0.0
 */
trait PathFormatter
{
    /**
     * Format the Path in a URL string
     *
     * @param Path $path
     * @param bool $has_authority_part does the URL as an authority part
     *
     * @return string
     */
    protected function formatPath(Path $path, $has_authority_part = false)
    {
        $str = $path->getUriComponent();
        if (!$has_authority_part) {
            return preg_replace(',^/+,', '/', $str);
        }

        if (!$path->isEmpty() && !$path->isAbsolute()) {
            return '/'.$str;
        }

        return $str;
    }
}
