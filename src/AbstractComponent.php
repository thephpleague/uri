<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Url;

/**
 * An abstract class to ease component manipulation
 *
 * @package League.url
 * @since   4.0.0
 */
abstract class AbstractComponent
{
    /**
     * Trait for ComponentTrait method
     */
    use Utilities\ComponentTrait;

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
        if (!empty($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * validate the incoming data
     *
     * @param  string $data
     *
     * @return string
     */
    protected function validate($data)
    {
        $data = filter_var($data, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        $this->assertValidString($data);

        return rawurldecode(trim($data));
    }

    /**
     * Check the string against RFC3986 rules
     *
     * @param string $data
     *
     * @throws \InvalidArgumentException If the string is invalid
     */
    protected function assertValidString($data)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->encode($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }
}
