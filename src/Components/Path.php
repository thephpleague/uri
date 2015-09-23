<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.url
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use League\Uri\Interfaces\Path as PathInterface;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Path extends AbstractComponent implements PathInterface
{
    use PathTrait;

    /**
     * @inheritdoc
     */
    protected static $characters_set = [
        '/', ':', '@', '!', '$', '&', "'", '%',
        '(', ')', '*', '+', ',', ';', '=', '?',
    ];

    /**
     * @inheritdoc
     */
    protected static $characters_set_encoded = [
        '%2F', '%3A', '%40', '%21', '%24', '%26', '%27', '%25',
        '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D', '%3F',
    ];

    /**
     * @inheritdoc
     */
    protected static $invalidCharactersRegex = ',[?#],';

    /**
     * new instance
     *
     * @param string $path the component value
     */
    public function __construct($path = '')
    {
        parent::__construct($this->validateString($path));
    }

    /**
     * validate the submitted data
     *
     * @param string $path
     *
     * @return array
     */
    protected function validate($path)
    {
        $this->assertValidComponent($path);

        $reserved = implode('', array_map(function ($char) {
            return preg_quote($char, '/');
        }, static::$characters_set));

        return preg_replace_callback(
            '/(?:[^'.$reserved.']+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'decodeSegmentPart'],
            $path
        );
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->encodePath(static::encode($this->data));
    }
}
