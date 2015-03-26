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

use League\Url\Interfaces\Component;

/**
 * An abstract class to ease component creation
 *
 * @package  League.url
 * @since  4.0.0
 */
abstract class AbstractComponent
{
    /**
     * The component data
     *
     * @var string
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->data = $this->validate((string) $data);
        }
    }

    /**
     * validate the incoming data
     *
     * @param  string $data
     *
     * @throws InvalidArgumentException If the supplied data is invalid
     *
     * @return string
     */
    abstract protected function validate($data);

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Component $component)
    {
        return $component->__toString() == $this->__toString();
    }

    /**
     * Returns a new object with the given value
     */
    public function withValue($data = null)
    {
        return new static($data);
    }
}
