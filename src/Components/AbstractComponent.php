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
namespace League\Url\Components;

use InvalidArgumentException;

/**
 *  A class to manipulate URL string-like component
 *
 *  @package League.url
 *  @since  3.0.0
 */
abstract class AbstractComponent implements ComponentInterface
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
        $this->data = $this->validate($data);
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
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * Validate a component
     *
     * @param mixed $data the component value to be validate
     *
     * @return string|null
     *
     * @throws InvalidArgumentException If The data is invalid
     */
    protected function validate($data)
    {
        return $this->sanitizeComponent($data);
    }

    /**
     * Sanitize a string component
     *
     * @param mixed $str
     *
     * @return string|null
     */
    protected function sanitizeComponent($str)
    {
        if (is_null($str)) {
            return $str;
        }
        $str = filter_var((string) $str, FILTER_UNSAFE_RAW, array('flags' => FILTER_FLAG_STRIP_LOW));
        $str = trim($str);

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(ComponentInterface $component)
    {
        return $this->__toString() == $component->__toString();
    }
}
