<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.2.0
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
     * @inheritdoc
     */
    public static function __set_state(array $properties)
    {
        return new static($properties['data']);
    }

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
     * Returns the component literal value.
     *
     * @return string|int|null
     */
    public function getContent()
    {
        if (null === $this->data) {
            return null;
        }

        return $this->encode($this->data);
    }

    /**
     * Returns the instance string representation; If the
     * instance is not defined an empty string is returned
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getContent();
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

    /**
     * Returns an instance with the specified string
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified data
     *
     * @param string $value
     *
     * @return static
     */
    public function modify($value)
    {
        if ($value === $this->getContent()) {
            return $this;
        }

        return new static($value);
    }
}
