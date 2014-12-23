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

/**
 *  A class to manipulate URL string-like component
 *
 *  @package League.url
 *  @since  3.0.0
 */
abstract class AbstractComponent
{
    /**
     * The component data
     *
     * @var string|null
     */
    protected $data;

    /**
     * The Constructor
     *
     * @param mixed $data the component data
     */
    public function __construct($data = null)
    {
        $this->set($data);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        if (is_null($data)) {
            $this->data = $data;

            return;
        }
        $data = filter_var((string) $data, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        $this->data = trim($data);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (is_null($this->data) || ! $this->data) {
            return null;
        }

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return str_replace(null, '', $this->get());
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(ComponentInterface $component)
    {
        return $this->__toString() == $component->__toString();
    }
}
