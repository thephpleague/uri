<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Components\HierarchicalPath as HierarchicalPathInterface;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class HierarchicalPath extends AbstractHierarchicalComponent implements HierarchicalPathInterface
{
    use RemoveDotSegmentsTrait;

    /**
     * {@inheritdoc}
     */
    protected static $characters_set = [
        '/', ':', '@', '!', '$', '&', "'",
        '(', ')', '*', '+', ',', ';', '=', '?',
    ];

    /**
     * {@inheritdoc}
     */
    protected static $characters_set_encoded = [
        '%2F', '%3A', '%40', '%21', '%24', '%26', '%27',
        '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D', '%3F',
    ];

    /**
     * {@inheritdoc}
     */
    protected static $invalidCharactersRegex = ',[?#],';

    /**
     * {@inheritdoc}
     */
    protected function init($str)
    {
        $str = $this->validateString($str);
        $this->assertValidComponent($str);
        $this->isAbsolute = self::IS_RELATIVE;
        if (static::$separator == mb_substr($str, 0, 1, 'UTF-8')) {
            $this->isAbsolute = self::IS_ABSOLUTE;
            $str = mb_substr($str, 1, mb_strlen($str), 'UTF-8');
        }

        $append_delimiter = false;
        if (static::$separator === mb_substr($str, -1, 1, 'UTF-8')) {
            $str = mb_substr($str, 0, -1, 'UTF-8');
            $append_delimiter = true;
        }

        $this->data = $this->validate($str);
        if ($append_delimiter) {
            $this->data[] = '';
        }
    }

    /**
     * validate the submitted data
     *
     * @param string $data
     *
     * @throws InvalidArgumentException If reserved characters are used
     *
     * @return array
     */
    protected function validate($data)
    {
        $data = array_values(array_filter(explode(static::$separator, $data), function ($value) {
            return !is_null($value);
        }));

        return array_map(function ($value) {
            return rawurldecode(filter_var($value, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]));
        }, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSegment($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    public function getContent()
    {
        $front_delimiter = '';
        if ($this->isAbsolute == self::IS_ABSOLUTE) {
            $front_delimiter = static::$separator;
        }

        return $front_delimiter.implode(static::$separator, array_map([$this, 'encode'], $this->data));
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function relativize(HierarchicalPathInterface $path)
    {
        $bSegments = explode(static::$separator, $this->withoutDotSegments()->__toString());
        $cSegments = explode(static::$separator, $path->withoutDotSegments()->__toString());
        if ('' == end($bSegments)) {
            array_pop($bSegments);
        }

        $key = 0;
        $res = [];
        while (isset($cSegments[$key], $bSegments[$key]) && $cSegments[$key] === $bSegments[$key]) {
            ++$key;
            $res[] = '..';
        }

        return static::createFromArray(array_merge($res, array_slice($cSegments, $key)));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutEmptySegments()
    {
        return $this->modify(preg_replace(',/+,', '/', $this->__toString()));
    }

    /**
     * {@inheritdoc}
     */
    public function hasTrailingSlash()
    {
        return !$this->isEmpty() && empty($this->getBasename());
    }

    /**
     * {@inheritdoc}
     */
    public function withTrailingSlash()
    {
        return $this->hasTrailingSlash() ? $this : $this->modify($this->__toString().static::$separator);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutTrailingSlash()
    {
        return !$this->hasTrailingSlash() ? $this : $this->modify(substr($this->__toString(), 0, -1));
    }

    /**
     * {@inheritdoc}
     */
    public function getBasename()
    {
        $data = $this->data;

        return (string) array_pop($data);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getExtension()
    {
        list($basename, ) = explode(';', $this->getBasename(), 2);

        return pathinfo($basename, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
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
        $extension = ltrim($extension, '.');
        if (strpos($extension, static::$separator)) {
            throw new InvalidArgumentException('an extension sequence can not contain a path delimiter');
        }

        return implode(static::$separator, $this->validate($extension));
    }
}
