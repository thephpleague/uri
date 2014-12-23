<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.2.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use League\Url\Interfaces\ComponentInterface;
use RuntimeException;

/**
 *  A class to manipulate URL Scheme component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Scheme extends AbstractComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        if (is_null($data)) {
            $this->data = null;
            return;
        }

        $data = filter_var((string) $data, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        $data = trim($data);
        $data = filter_var($data, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-z][a-z0-9+-.]+$/i']]);
        if (! $data) {
            throw new RuntimeException('This class only deals with http URL');
        }

        $this->data = strtolower($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $value = $this->__toString();
        if ('' != $value) {
            $value .= '://';
        }

        return $value;
    }
}
