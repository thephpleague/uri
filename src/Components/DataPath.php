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
use League\Uri\Interfaces\DataPath as DataPathInterface;
use RuntimeException;
use SplFileObject;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class DataPath extends Path implements DataPathInterface
{
    const DEFAULT_MIMETYPE = 'text/plain';

    const DEFAULT_PARAMETER = 'charset=us-ascii';

    const BINARY_PARAMETER = 'base64';

    /**
     * Data Path properties
     *
     * @var array
     */
    protected $data = [
        'mimetype' => self::DEFAULT_MIMETYPE,
        'parameters' => [self::DEFAULT_PARAMETER],
        'isBinaryData' => false,
        'data' => '',
    ];


    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data['data'];
    }

    /**
     * @inheritdoc
     */
    public function isBinaryData()
    {
        return $this->data['isBinaryData'];
    }

    /**
     * @inheritdoc
     */
    public function getMimeType()
    {
        return $this->data['mimetype'];
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return implode(';', $this->data['parameters']);
    }

    /**
     * @inheritdoc
     */
    public function getMediaType()
    {
        return $this->getMimeType().';'.$this->getParameters();
    }

    /**
     * @inheritdoc
     */
    public function save($path, $mode = 'w')
    {
        $file = new SplFileObject($path, $mode);
        $data = $this->isBinaryData() ? base64_decode($this->data['data']) : rawurldecode($this->data['data']);
        $file->fwrite($data);

        return $file;
    }

    /**
     * @inheritdoc
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->format(
            $this->getMimeType(),
            $this->getParameters(),
            $this->isBinaryData(),
            $this->getData()
        );
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

        return $this->encodePath($str.','.$data);
    }

    /**
     * @inheritdoc
     */
    public function toBinary()
    {
        if ($this->isBinaryData()) {
            return $this;
        }

        return new static($this->format(
            $this->getMimeType(),
            $this->getParameters(),
            !$this->isBinaryData(),
            base64_encode(rawurldecode($this->getData()))
        ));
    }

    /**
     * @inheritdoc
     */
    public function toAscii()
    {
        if (!$this->isBinaryData()) {
            return $this;
        }

        return new static($this->format(
            $this->getMimeType(),
            $this->getParameters(),
            !$this->isBinaryData(),
            rawurlencode(base64_decode($this->getData()))
        ));
    }

    /**
     * @inheritdoc
     */
    public function withParameters($parameters)
    {
        if ($parameters == $this->getParameters()) {
            return $this;
        }

        return new static($this->format(
            $this->getMimeType(),
            $parameters,
            $this->isBinaryData(),
            $this->getData()
        ));
    }

    /**
     * Create a new instance from a file path
     *
     * @param string $path
     *
     * @throws RuntimeException If the File is not readable
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
     * @inheritdoc
     */
    protected function init($path)
    {
        if ('' === $path) {
            $path = self::DEFAULT_MIMETYPE.';'.self::DEFAULT_PARAMETER.',';
        }
        $this->assertValidComponent($path);
        $this->data = $this->validate($path);
    }

    /**
     * @inheritdoc
     */
    protected function assertValidComponent($path)
    {
        parent::assertValidComponent($path);
        if (!mb_detect_encoding($path, 'US-ASCII', true)
            || false === strpos($path, ',')
            || false !== strpos($path, '\n')
        ) {
            throw new InvalidArgumentException(
                sprintf('The submitted path `%s` is invalid according to RFC2937', $path)
            );
        }
    }

    /**
     * Validate the string
     *
     * @param string $path
     * @param string[]
     *
     * @return array
     */
    protected function validate($path)
    {
        $res = explode(',', $path, 2);
        $matches = ['mediatype' => array_shift($res), 'data' => array_shift($res)];
        $mimeType = self::DEFAULT_MIMETYPE;
        $parameters = static::DEFAULT_PARAMETER;
        if (!empty($matches['mediatype'])) {
            $mediatype = explode(';', $matches['mediatype'], 2);
            $mimeType = array_shift($mediatype);
            $parameters = (string) array_shift($mediatype);
        }
        $this->filterMimeType($mimeType);
        $extracts = $this->extractBinaryFlag($parameters);
        $this->filterParameters($extracts['parameters']);
        $this->filterData($matches['data'], $extracts['isBinaryData']);

        return [
            'mimetype' => $mimeType,
            'parameters' => explode(';', $extracts['parameters']),
            'isBinaryData' => $extracts['isBinaryData'],
            'data' => $matches['data'],
        ];
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
    }

    /**
     * Extract and set the binary flag from the parameters if it exists
     *
     * @param string $parameters
     *
     * @throws InvalidArgumentException If the parameter string is invalid
     *
     * @return array
     */
    protected function extractBinaryFlag($parameters)
    {
        $res = ['parameters' => static::DEFAULT_PARAMETER, 'isBinaryData' => false];
        if ('' === $parameters) {
            return $res;
        }

        if (!preg_match(',(;|^)'.static::BINARY_PARAMETER.'$,', $parameters, $matches)) {
            $res['parameters'] = $parameters;

            return $res;
        }

        $res['isBinaryData'] = true;
        $parameters = mb_substr($parameters, 0, - strlen($matches[0]));
        if (!empty($parameters)) {
            $res['parameters'] = $parameters;
        }

        return $res;
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
        $parameters = explode(';', $parameters);
        foreach ($parameters as $value) {
            if (0 === strpos($value, static::BINARY_PARAMETER) || 2 != count(explode('=', $value))) {
                throw new InvalidArgumentException('Invalid mediatype');
            }
        }
    }

    /**
     * Validate the media body
     *
     * @throws InvalidArgumentException If the data is invalid
     */
    protected function filterData($data, $isBinaryData)
    {
        if (!$isBinaryData) {
            return;
        }
        $res = base64_decode($data, true);
        if (false === $res || $data !== base64_encode($res)) {
            throw new InvalidArgumentException('The path data is invalid');
        }
    }
}
