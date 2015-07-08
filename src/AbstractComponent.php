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
namespace League\Uri;

use InvalidArgumentException;

/**
 * An abstract class to ease component manipulation
 *
 * @package League.url
 * @since   4.0.0
 */
abstract class AbstractComponent
{
    /**
     * common immutable value object methods
     */
    use Types\ImmutableValueObject;

    /**
     * Invalid Characters list
     *
     * @var string
     */
    protected static $invalidCharactersRegex;

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
    public function __construct($data = '')
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
        $this->assertValidComponent($data);

        return rawurldecode(trim($data));
    }

    /**
     * Check the string against RFC3986 rules
     *
     * @param string $data
     *
     * @throws \InvalidArgumentException If the string is invalid
     */
    protected function assertValidComponent($data)
    {
        if (!empty(static::$invalidCharactersRegex) && preg_match(static::$invalidCharactersRegex, $data)) {
            throw new InvalidArgumentException('The component contains invalid characters');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return static::encode($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getLiteral()
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
}
