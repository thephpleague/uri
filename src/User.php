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

/**
 * Value object representing a URL user component.
 *
 * @package League.url
 * @since  1.0.0
 */
class User extends AbstractComponent implements Interfaces\Component
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
        if (preg_match('/[:@]/', $data)) {
            throw new InvalidArgumentException('The URL user component can not contain the URL pass component');
        }

        return rawurldecode(trim($data));
    }
}
