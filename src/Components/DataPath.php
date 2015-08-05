<?php

/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Components\DataPath as DataPathInterface;
use League\Uri\Types\ImmutableComponentTrait;
use RuntimeException;
use SplFileObject;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @since 1.0.0
 */
class DataPath implements DataPathInterface
{
    use ImmutableComponentTrait;

    const DEFAULT_MIMETYPE = 'text/plain';

    const DEFAULT_PARAMETER = 'charset=us-ascii';

    const BINARY_PARAMETER = 'base64';

    /**
     * The File Data
     *
     * @var string
     */
    protected $data;

    /**
     * The File MimeType
     *
     * @var string
     */
    protected $mimeType = self::DEFAULT_MIMETYPE;

    /**
     * The File optional parameters
     *
     * @var string[]
     */
    protected $parameters = [self::DEFAULT_PARAMETER];

    /**
     * is the file save as a binary data
     *
     * @var bool
     */
    protected $isBinaryData = false;

    /**
     * a new Media Instance
     *
     * @param string $str
     */
    public function __construct($str = null)
    {
        if (empty($str)) {
            $str = '';
        }
        $str = $this->validateString($str);
        if (!empty($str)) {
            $this->validate($this->extractPathParts($str));
        }
    }

    /**
     * Extract Path part according to RFC2937
     *
     * @param string $str
     *
     * @throws InvalidArgumentException if the string is not valid according to RFC2937
     *
     * @return string[]
     */
    protected function extractPathParts($str)
    {
        if (!mb_detect_encoding($str, 'US-ASCII', true)
            || !preg_match('|^(?<mediatype>.*)?,(?<data>.*)$|i', $str, $matches)
        ) {
            throw new InvalidArgumentException(
                sprintf('The submitted path `%s` is invalid according to RFC2937', $str)
            );
        }

        return $matches;
    }

    /**
     * Validate the string
     *
     * @param string[] $matches
     *
     * @throws InvalidArgumentException if the object can not be instantiated
     */
    protected function validate($matches)
    {
        $mimeType = self::DEFAULT_MIMETYPE;
        $parameters = static::DEFAULT_PARAMETER;
        if (!empty($matches['mediatype'])) {
            $data = explode(';', $matches['mediatype'], 2);
            $mimeType = array_shift($data);
            $parameters = (string) array_pop($data);
        }
        $parameters = trim($parameters);
        $this->filterMimeType($mimeType);
        $this->data = $matches['data'];
        $parameters = $this->extractBinaryFlag($parameters);
        $this->filterParameters($parameters);
        if (!$this->validateData()) {
            throw new InvalidArgumentException(sprintf('The submitted data `%s` is invalid', $matches['data']));
        }
    }

    /**
     * Filter and Set the mimeType property
     *
     * @param string $mimeType
     *
     * @throws InvalidArgumentException If the mimetype is invalid
     */
    protected function filterMimeType($mimeType)
    {
        if (!preg_match(',^[a-z-\/+]+$,i', $mimeType)) {
            throw new InvalidArgumentException(sprintf('invalid mimeType, `%s`', $mimeType));
        }

        $this->mimeType = $mimeType;
    }

    /**
     * Extract and set the binary flag from the parameters if it exists
     *
     * @param string $str
     *
     * @throws InvalidArgumentException If the parameter string is invalid
     *
     * @return string
     */
    protected function extractBinaryFlag($str)
    {
        $parameters = explode(';', $str);
        $res = array_keys($parameters, static::BINARY_PARAMETER, true);
        $binCount = count($res);
        if ($binCount > 1) {
            throw new InvalidArgumentException(sprintf('The parameter string is invalid `%s`', $str));
        }

        if (empty($binCount)) {
            return $str;
        }

        $this->isBinaryData = true;

        return implode(';', array_filter($parameters, function ($value) {
            return static::BINARY_PARAMETER !== $value;
        }));
    }

    /**
     * Filter and Set the Parameter property
     *
     * @param string $parameters
     *
     * @throws InvalidArgumentException If the parameter is invalid
     */
    protected function filterParameters($parameters)
    {
        if (empty($parameters)) {
            $this->parameters = [static::DEFAULT_PARAMETER];
            return;
        }

        $parameters = explode(';', $parameters);
        $checkValidParameters = array_filter($parameters, function ($value) {
            return 0 === strpos($value, static::BINARY_PARAMETER.'=') || 2 != count(explode('=', $value));
        });

        if (!empty($checkValidParameters)) {
            throw new InvalidArgumentException('Invalid mediatype');
        }

        $this->parameters = $parameters;
    }

    /**
     * Validate the media body
     *
     * @return bool
     */
    protected function validateData()
    {
        if (!$this->isBinaryData) {
            return true;
        }

        $res = base64_decode($this->data, true);

        return false !== $res && $this->data === base64_encode($res);
    }

    /**
     * Create a new instance from a file path
     *
     * @param string $path
     *
     * @throws \RuntimeException If the File is not readable
     *
     * @return static
     */
    public static function createFromPath($path)
    {
        if (!is_readable($path)) {
            throw new RuntimeException(sprintf('The specified file `%s` is not readable', $path));
        }

        $data = file_get_contents($path);
        $res = (new \finfo(FILEINFO_MIME))->file($path);
        return new static($res.';'.static::BINARY_PARAMETER.','.base64_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return implode(';', $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function isBinaryData()
    {
        return $this->isBinaryData;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->format(
            $this->mimeType,
            $this->getParameters(),
            $this->isBinaryData,
            $this->data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function withoutDotSegments()
    {
        return $this;
    }

    /**
     * Format the DataURI string
     *
     * @param string $mimeType
     * @param string $parameters
     * @param bool   $isBinaryData
     * @param string $data
     *
     * @return string
     */
    protected function format($mimeType, $parameters, $isBinaryData, $data)
    {
        $str = $mimeType;
        if (!empty($parameters)) {
            $str .= ';'.$parameters;
        }
        if ($isBinaryData) {
            $str .= ';'.static::BINARY_PARAMETER;
        }

        return $str.','.$data;
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function isNull()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function withParameters($parameters)
    {
        if ($parameters == $this->getParameters()) {
            return $this;
        }

        return new static($this->format(
            $this->mimeType,
            $parameters,
            $this->isBinaryData,
            $this->data
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary()
    {
        if ($this->isBinaryData) {
            return $this;
        }

        return new static($this->format(
            $this->mimeType,
            $this->getParameters(),
            !$this->isBinaryData,
            base64_encode(rawurldecode($this->data))
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        if (!$this->isBinaryData) {
            return $this;
        }

        return new static($this->format(
            $this->mimeType,
            $this->getParameters(),
            !$this->isBinaryData,
            rawurlencode(base64_decode($this->data))
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function save($path, $mode = 'w')
    {
        $file = new SplFileObject($path, $mode);
        $data = $this->isBinaryData ? base64_decode($this->data) : rawurldecode($this->data);
        $file->fwrite($data);

        return $file;
    }
}
