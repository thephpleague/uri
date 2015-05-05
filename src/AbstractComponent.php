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
use League\Url\Util;

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
     * Trait for ComponentTrait method
     */
    use Util\ComponentTrait;

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
     * @throws \InvalidArgumentException If the supplied data is invalid
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
     * {@inheritdoc}
     */
    public function withValue($value)
    {
        return new static($value);
    }
}
