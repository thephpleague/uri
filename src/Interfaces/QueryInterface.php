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
namespace League\Url\Interfaces;

/**
 * A common interface for URL Query component
 *
 *  @package League.url
 *  @since  3.0.0
 */
interface QueryInterface extends ComponentInterface
{
    /**
     * return a array representation of the data
     *
     * @return array
     */
    public function toArray();

    /**
     * return the array keys
     *
     * @return array
     */
    public function keys();

    /**
     * Query Parameter setter using an array
     *
     * @param array $data
     */
    public function modify($data);

    /**
     * Return a Parameter Parameter
     *
     * @param string $key     the query parameter key
     * @param mixed  $default the query parameter default value
     *
     * @return mixed
     */
    public function getParameter($key, $default = null);

    /**
     * Parameter Parameter Setter
     *
     * @param string $key   the query parameter key
     * @param mixed  $value the query parameter value
     */
    public function setParameter($key, $value);
}
