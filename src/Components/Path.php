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

    public function __toString()
    {
        return $this->encodePath($this->data);
    }

    /**
     * validate the submitted data
     *
     * @param string $path
     *
     * @return string
     */
    protected function validate($path)
    {
        $this->assertValidComponent($path);

        return $this->decodePath($path);
    }
}
