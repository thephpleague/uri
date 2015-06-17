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
namespace League\Url;

use InvalidArgumentException;
use League\Url\Interfaces;

/**
 * Value object representing a URL pass component.
 *
 * @package League.url
 * @since  1.0.0
 */
class Pass extends AbstractComponent implements Interfaces\Component
{
    /**
     * validate the incoming data
     *
     * @param  string $data
     *
     * @return string
     */
    protected function validate($data)
    {
        $data = filter_var($data, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        if (strpos($data, '@') !== false) {
            throw new InvalidArgumentException('The URL pass component contains invalid characters');
        }

        return rawurldecode(trim($data));
    }
}
