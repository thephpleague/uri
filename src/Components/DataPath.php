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
class DataPath extends AbstractComponent implements DataPathInterface
{
    use PathTrait;

    const DEFAULT_MIMETYPE = 'text/plain';

    const DEFAULT_PARAMETER = 'charset=us-ascii';

    const BINARY_PARAMETER = 'base64';

    const REGEXP_MIMETYPE = ',^\w+/[-.\w]+(?:\+[-.\w]+)?$,';

    /**
     * The mediatype mimetype
     *
     * @var string
     */
    protected $mimetype;

    /**
     * The mediatype parameters
     *
     * @var string[]
     */
    protected $parameters;

    /**
     * Is the Document bas64 encoded
     *
     * @var bool
     */
    protected $isBinaryData;

    /**
     * The document string representation
     *
     * @var string
     */
    protected $document;

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
        $path = $this->validateString($path);
        if ('' === $path) {
            $path = static::DEFAULT_MIMETYPE.';'.static::DEFAULT_PARAMETER.',';
        }
        $this->setComponentProperties($path);
    }

    /**
     * Set Data Path properties
     *
     * @param string $path
     */
    protected function setComponentProperties($path)
    {
        $this->assertValidComponent($path);
        $parts = explode(',', $path, 2);
        $mediatype = array_shift($parts);
        $this->document = (string) array_shift($parts);
        $mimetype = static::DEFAULT_MIMETYPE;
        $parameters = static::DEFAULT_PARAMETER;
        if (!empty($mediatype)) {
            $mediatype = explode(';', $mediatype, 2);
            $mimetype = array_shift($mediatype);
            $parameters = (string) array_shift($mediatype);
        }
        $this->mimetype = $this->filterMimeType($mimetype);
        $this->parameters = $this->filterParameters($parameters);
        if ($this->isBinaryData) {
            $this->validateDocument();
        }
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
     * Filter the mimeType property
     *
     * @param string $mimetype
     *
     * @throws InvalidArgumentException If the mimetype is invalid
     *
     * @return string
     */
    protected function filterMimeType($mimetype)
    {
        if (!preg_match(static::REGEXP_MIMETYPE, $mimetype)) {
            throw new InvalidArgumentException(sprintf('invalid mimeType, `%s`', $mimetype));
        }

        return $mimetype;
    }

    /**
     * Extract and set the binary flag from the parameters if it exists
     *
     * @param string $parameters
     *
     * @throws InvalidArgumentException If the mediatype parameters contain invalid data
     *
     * @return string[]
     */
    protected function filterParameters($parameters)
    {
        $this->isBinaryData = false;
        if ('' == $parameters) {
            return [static::DEFAULT_PARAMETER];
        }

        if (preg_match(',(;|^)'.static::BINARY_PARAMETER.'$,', $parameters, $matches)) {
            $parameters = mb_substr($parameters, 0, - strlen($matches[0]));
            $this->isBinaryData = true;
        }

        $params = array_filter(explode(';', $parameters));
        if (!empty(array_filter($params, [$this, 'validateParameter']))) {
            throw new InvalidArgumentException(sprintf('invalid mediatype parameters, `%s`', $parameters));
        }

        return $params;
    }

    /**
     * Validate mediatype parameter
     *
     * @param string $parameter a mediatype parameter
     *
     * @return bool
     */
    protected function validateParameter($parameter)
    {
        $properties = explode('=', $parameter);

        return 2 != count($properties) || mb_strtolower($properties[0], 'UTF-8') == static::BINARY_PARAMETER;
    }

    /**
     * Validate the path document string representation
     *
     * @throws InvalidArgumentException If the data is invalid
     */
    protected function validateDocument()
    {
        $res = base64_decode($this->document, true);
        if (false === $res || $this->document !== base64_encode($res)) {
            throw new InvalidArgumentException('The path data is invalid');
        }
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->document;
    }

    /**
     * @inheritdoc
     */
    public function isBinaryData()
    {
        return $this->isBinaryData;
    }

    /**
     * @inheritdoc
     */
    public function getMimeType()
    {
        return $this->mimetype;
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return implode(';', $this->parameters);
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
        $data = $this->isBinaryData ? base64_decode($this->document) : rawurldecode($this->document);
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
            $this->mimetype,
            $this->getParameters(),
            $this->isBinaryData,
            $this->document
        );
    }

    /**
     * Format the DataURI string
     *
     * @param string $mimetype
     * @param string $parameters
     * @param bool   $isBinaryData
     * @param string $data
     *
     * @return string
     */
    protected static function format($mimetype, $parameters, $isBinaryData, $data)
    {
        if ('' != $parameters) {
            $parameters = ';'.$parameters;
        }

        if ($isBinaryData) {
            $parameters .= ';'.static::BINARY_PARAMETER;
        }

        return static::encode($mimetype.$parameters.','.$data);
    }

    /**
     * @inheritdoc
     */
    public function toBinary()
    {
        if ($this->isBinaryData) {
            return $this;
        }

        return new static($this->format(
            $this->mimetype,
            $this->getParameters(),
            !$this->isBinaryData,
            base64_encode(rawurldecode($this->document))
        ));
    }

    /**
     * @inheritdoc
     */
    public function toAscii()
    {
        if (!$this->isBinaryData) {
            return $this;
        }

        return new static($this->format(
            $this->mimetype,
            $this->getParameters(),
            !$this->isBinaryData,
            rawurlencode(base64_decode($this->document))
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

        if (preg_match(',(;|^)'.static::BINARY_PARAMETER.'$,', $parameters)) {
            throw new InvalidArgumentException('The parameter data is invalid');
        }

        return new static($this->format(
            $this->mimetype,
            $parameters,
            $this->isBinaryData,
            $this->document
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

        return new static(static::format(
            str_replace(' ', '', (new \finfo(FILEINFO_MIME))->file($path)),
            '',
            true,
            base64_encode(file_get_contents($path))
        ));
    }
}
