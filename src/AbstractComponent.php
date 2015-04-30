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
        $data = $this->validateString($data);
        if (! empty($data)) {
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
        if (! empty($this->data)) {
            return $this->data;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * validate a string
     *
     * @param  mixed $str
     *
     * @throws InvalidArgumentException if the submitted data can not be converted to string
     *
     * @return string
     */
    protected function validateString($str)
    {
        if (is_null($str) || is_scalar($str) || (is_object($str) && method_exists($str, '__toString'))) {
            return trim($str);
        }

        throw new InvalidArgumentException(sprintf(
            'Data passed must be a valid string; received "%s"',
            (is_object($str) ? get_class($str) : gettype($str))
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Component $component)
    {
        return $component->getUriComponent() == $this->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function withValue($value)
    {
        return new static($value);
    }
}
