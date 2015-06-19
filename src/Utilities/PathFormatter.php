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
namespace League\Url\Utilities;

use League\Url\Interfaces\Path;

/**
 * A trait to set and get immutable value
 *
 * @package League.url
 * @since 4.0.0
 */
trait PathFormatter
{
    /**
     * Format the Path in a URL string
     *
     * @param  Path $path
     * @param  boolean    $has_authority_part does the URL as an authority part
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
