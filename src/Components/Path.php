<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.0
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

        return preg_replace_callback(
            $this->getReservedRegex(),
            [$this, 'decodeSegmentPart'],
            $path
        );
    }

    /**
     * Returns the instance string representation; If the
     * instance is not defined an empty string is returned
     *
     * @return string
     */
    public function __toString()
    {
        return static::encode($this->data);
    }
}
