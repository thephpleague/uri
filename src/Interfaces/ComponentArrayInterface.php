<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Interfaces;

interface ComponentArrayInterface extends ComponentInterface
{

    /**
     * Return the component as an array
     *
     * @return array
     */
    public function toArray();

    /**
     * Return the index for a given $value if it is present in the Segment
     * or null
     *
     * @param mixed $value
     *
     * @return null|string
     */
    public function contains($value);
}
