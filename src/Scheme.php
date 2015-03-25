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
use League\Url\Interfaces\Component;

/**
* A class to manipulate URL Scheme component
*
* @package League.url
* @since 1.0.0
*/
class Scheme extends AbstractComponent implements Component
{
    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        if (! preg_match('/^[a-z][a-z0-9+-.]+$/i', $data)) {
            throw new InvalidArgumentException('The submitted data is invalid');
        }

        return strtolower($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $data = $this->__toString();
        if (empty($data)) {
            return $data;
        }
        return $data.'://';
    }
}
