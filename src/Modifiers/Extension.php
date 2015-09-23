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
namespace League\Uri\Modifiers;

use InvalidArgumentException;
use League\Uri\Components\HierarchicalPath;

/**
 * Path component extension modifier
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Extension extends AbstractPathModifier
{
    /**
     * The extension to use for URI modification
     *
     * @var string
     */
    protected $extension;

    /**
     * New instance
     *
     * @param string $extension
     */
    public function __construct($extension)
    {
        $this->extension = $this->filterExtension($extension);
    }

    /**
     * filter and validate the extension to use
     *
     * @param string $extension
     *
     * @throws InvalidArgumentException if the extension is not valid
     *
     * @return string
     */
    protected function filterExtension($extension)
    {
        if (0 === strpos($extension, '.') || false !== strpos($extension, '/')) {
            throw new InvalidArgumentException(
                'extension must be string sequence without a leading `.` and the path separator `/` characters'
            );
        }

        $extension = filter_var($extension, FILTER_SANITIZE_STRING, ['options' => FILTER_FLAG_STRIP_LOW]);

        return trim($extension);
    }

    /**
     * Return a new instance with a different extension to use
     *
     * @param string $extension
     *
     * @return self
     */
    public function withExtension($extension)
    {
        $clone = clone $this;
        $clone->extension = $this->filterExtension($extension);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    protected function modify($str)
    {
        return (string) (new HierarchicalPath($str))->withExtension($this->extension);
    }
}
