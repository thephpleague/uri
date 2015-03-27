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
 * An abstract class to ease component creation
 *
 * @package League.url
 * @since  3.0.0
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
     * new instance
     *
     * @param string $data the component value
     */
    public function __construct($data = null)
    {
        $data = trim($data);
        if ('' != $data) {
            $this->data = $this->validate($data);
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
    protected function validate($data)
    {
        if (ctype_alnum($data)) {
            return $data;
        }

        $component  = preg_replace('/%[0-9a-f]{2}/i', '', $data);
        $unreserved = '-a-z0-9._~';
        $subdelims  = preg_quote('!$&\'()*+,;=]/', '/');

        if (! preg_match('/^['.$unreserved.$subdelims.']+$/i', $component)) {
            throw new InvalidArgumentException('The submitted user info is invalid');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return ! empty($this->data) ? $this->data : null;
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
     *
     * @return static
     */
    public function withValue($data = null)
    {
        return new static($data);
    }
}
