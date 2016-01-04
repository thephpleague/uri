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

use InvalidArgumentException;
use League\Uri\Interfaces\HierarchicalPath as HierarchicalPathInterface;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class HierarchicalPath extends AbstractHierarchicalComponent implements HierarchicalPathInterface
{
    use PathTrait;

    /**
     * @inheritdoc
     */
    protected static $separator = '/';

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
     * New Instance
     *
     * @param string $path
     */
    public function __construct($path = '')
    {
        $path = $this->validateString($path);
        $this->assertValidComponent($path);
        $this->isAbsolute = self::IS_RELATIVE;
        if (static::$separator == mb_substr($path, 0, 1, 'UTF-8')) {
            $this->isAbsolute = self::IS_ABSOLUTE;
            $path = mb_substr($path, 1, mb_strlen($path), 'UTF-8');
        }

        $append_delimiter = false;
        if (static::$separator === mb_substr($path, -1, 1, 'UTF-8')) {
            $path = mb_substr($path, 0, -1, 'UTF-8');
            $append_delimiter = true;
        }

        $this->data = $this->validate($path);
        if ($append_delimiter) {
            $this->data[] = '';
        }
    }

    /**
     * validate the submitted data
     *
     * @param string $data
     *
     * @return array
     */
    protected function validate($data)
    {
        $filterSegment = function ($segment) {
            return isset($segment);
        };

        $data = preg_replace_callback(
            $this->getReservedRegex(),
            [$this, 'decodeSegmentPart'],
            $data
        );

        return array_filter(explode(static::$separator, $data), $filterSegment);
    }

    /**
     * @inheritdoc
     */
    public function getSegment($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    public function __toString()
    {
        $front_delimiter = '';
        if ($this->isAbsolute == self::IS_ABSOLUTE) {
            $front_delimiter = static::$separator;
        }
        $path = $front_delimiter.implode(static::$separator, array_map([$this, 'encode'], $this->data));

        return $this->encode($path);
    }

    /**
     * @inheritdoc
     */
    public function append($component)
    {
        $source = $this->toArray();
        if (!empty($source) && '' === end($source)) {
            array_pop($source);
        }

        return $this->newCollectionInstance(array_merge(
            $source,
            $this->validateComponent($component)->toArray()
        ));
    }

    /**
     * @inheritdoc
     */
    public function getBasename()
    {
        $data = $this->data;

        return (string) array_pop($data);
    }

    /**
     * @inheritdoc
     */
    public function getDirname()
    {
        return str_replace(
            ['\\', "\0"],
            [static::$separator, '\\'],
            dirname(str_replace('\\', "\0", $this->__toString()))
        );
    }

    /**
     * @inheritdoc
     */
    public function getExtension()
    {
        list($basename, ) = explode(';', $this->getBasename(), 2);

        return pathinfo($basename, PATHINFO_EXTENSION);
    }

    /**
     * @inheritdoc
     */
    public function withExtension($extension)
    {
        $extension = $this->formatExtension($extension);
        $segments = $this->toArray();
        $basename = array_pop($segments);
        $parts = explode(';', $basename, 2);
        $basenamePart = array_shift($parts);
        if (empty($basenamePart)) {
            return $this;
        }

        $newBasename = $this->buildBasename($basenamePart, $extension, array_shift($parts));
        if ($basename === $newBasename) {
            return $this;
        }
        $segments[] = $newBasename;

        return $this->newCollectionInstance($segments);
    }

    /**
     * create a new basename with a new extension
     *
     * @param string $basenamePart  the basename file part
     * @param string $extension     the new extension to add
     * @param string $parameterPart the basename parameter part
     *
     * @return string
     */
    protected function buildBasename($basenamePart, $extension, $parameterPart)
    {
        $length = mb_strrpos($basenamePart, '.'.pathinfo($basenamePart, PATHINFO_EXTENSION), 'UTF-8');
        if (false !== $length) {
            $basenamePart = mb_substr($basenamePart, 0, $length, 'UTF-8');
        }

        if (!empty($parameterPart)) {
            $parameterPart = ";$parameterPart";
        }

        if (!empty($extension)) {
            $extension = ".$extension";
        }

        return $basenamePart.$extension.$parameterPart;
    }

    /**
     * validate and format the given extension
     *
     * @param string $extension the new extension to use
     *
     * @throws InvalidArgumentException If the extension is not valid
     *
     * @return string
     */
    protected function formatExtension($extension)
    {
        if (0 === strpos($extension, '.')) {
            throw new InvalidArgumentException('an extension sequence can not contain a leading `.` character');
        }

        if (strpos($extension, static::$separator)) {
            throw new InvalidArgumentException('an extension sequence can not contain a path delimiter');
        }

        return implode(static::$separator, $this->validate($extension));
    }
}
