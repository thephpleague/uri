<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.1
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use League\Uri\Types\ImmutableComponentTrait;

/**
 * An abstract class to ease component manipulation
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class AbstractComponent
{
    use ImmutableComponentTrait;

    /**
     * The component data
     *
     * @var int|string
     */
    protected $data;

    /**
     * new instance
     *
     * @param string|null $data the component value
     */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->init($data);
        }
    }

    /**
     * Set data.
     *
     * @param mixed $data The data to set.
     */
    protected function init($data)
    {
        $data = $this->validateString($data);
        $this->data = $this->validate($data);
    }

    /**
     * validate the incoming data
     *
     * @param string $data
     *
     * @return string
     */
    protected function validate($data)
    {
        $data = filter_var($data, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        $this->assertValidComponent($data);

        return rawurldecode(trim($data));
    }

    /**
     * Returns the instance string representation; If the
     * instance is not defined an empty string is returned
     *
     * @return string
     */
    public function __toString()
    {
        return $this->encode($this->data);
    }

    /**
     * Returns the instance string representation
     * with its optional URI delimiters
     *
     * @return string
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }
}
