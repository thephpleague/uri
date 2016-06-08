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

use League\Uri\Interfaces\Fragment as FragmentInterface;

/**
 * Value object representing a URI fragment component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Fragment extends AbstractComponent implements FragmentInterface
{

    /**
     * Preserve the delimiter
     *
     * @var bool
     */
    protected $preserveDelimiter = false;

    /**
     * new instance
     *
     * @param string|null $data the component value
     */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->preserveDelimiter = true;
            $this->data = $this->decodeQueryFragment($this->validateString($data));
        }
    }

    /**
     * @inheritdoc
     */
    public static function __set_state(array $properties)
    {
        $component = new static($properties['data']);
        $component->preserveDelimiter = $properties['preserveDelimiter'];

        return $component;
    }

    /**
     * @inheritdoc
     */
    public function __debugInfo()
    {
        return ['fragment' => $this->__toString()];
    }

    /**
     * Returns the component literal value
     *
     * @return string|null
     */
    public function getContent()
    {
        if (null === $this->data && false === $this->preserveDelimiter) {
            return null;
        }

        return $this->encodeQueryFragment($this->data);
    }

    /**
     * Returns the instance string representation
     * with its optional URI delimiters
     *
     * @return string
     */
    public function getUriComponent()
    {
        $component = $this->__toString();
        if ($this->preserveDelimiter) {
            return FragmentInterface::DELIMITER.$component;
        }

        return $component;
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
        if (null === $value && $value === $this->getContent()) {
            return $this;
        }

        if ($value === $this->__toString()) {
            return $this;
        }

        return new static($value);
    }
}
