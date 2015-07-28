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
namespace League\Uri\Schemes;

use InvalidArgumentException;
use League\Uri\Components\Parameters;
use League\Uri\Components\Scheme;
use League\Uri\Interfaces;
use SplFileObject;

/**
 * Value object representing Data Uri.
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
class Data extends Generic\AbstractUri implements Interfaces\Schemes\Uri
{
    const DEFAULT_MIMETYPE = 'text/plain';

    const DEFAULT_PARAMETER = 'charset=us-ascii';

    const BINARY_PARAMETER = 'base64';

    /**
     * The encoded data
     *
     * @var string
     */
    protected $data;

    /**
     * The data mimetype
     *
     * @var string
     */
    protected $mimetype;

    /**
     * The URI mediatype parameters
     *
     * @var Interfaces\Parameters
     */
    protected $parameters;

    /**
     * A new Data URI instance
     * @param Interfaces\Scheme     $scheme
     * @param string                $mimetype   The Data URI associated MimeType
     * @param Interfaces\Parameters $parameters The parameter associated with the MediaType
     * @param string                $data       The encoded data
     */
    public function __construct(
        Interfaces\Scheme $scheme,
        $mimetype,
        Interfaces\Parameters $parameters,
        $data
    ) {
        $this->mimetype = $this->setMimetype($mimetype);
        $this->parameters = $parameters;
        $this->scheme = $scheme;
        $this->data = $data;
        $this->assertValidObject();
    }

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        return !(
            'data' !== $this->scheme->__toString()
            || !$this->validateParameters($this->parameters)
            || !$this->validateData($this->data)
        );
    }

    /**
     * Validate the Parameter object
     *
     * @param Interfaces\Parameters $parameters
     *
     * @return bool
     */
    protected function validateParameters(Interfaces\Parameters $parameters)
    {
        foreach ($parameters as $key => $value) {
            if ($key !== static::BINARY_PARAMETER && null === $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate the string data
     *
     * @param string $data
     *
     * @return bool
     */
    protected function validateData($data)
    {
        if (!$this->parameters->hasKey(static::BINARY_PARAMETER)) {
            return true;
        }

        $res = base64_decode($data, true);

        return (false !== $res
            && preg_match('|^[a-z0-9\/\r\n+]*={0,2}$|i', $data)
            && $data === base64_encode($res));
    }

    /**
     * Validate the proposed mimetype
     *
     * @param string $mimetype
     *
     * @throws InvalidArgumentException if the mimetype is not well formed
     *
     * @return string
     */
    protected function setMimetype($mimetype)
    {
        if (empty($mimetype)) {
            return static::DEFAULT_MIMETYPE;
        }

        if (!preg_match(',^[a-z-\/+]+$,i', $mimetype)) {
            throw new InvalidArgumentException(sprintf('invalid mimetype, `%s`', $mimetype));
        }

        return $mimetype;
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
    public function getSchemeSpecificPart()
    {
        return $this->mimetype.$this->parameters->getUriComponent().','.$this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function save($path, $mode = 'w')
    {
        $file = new SplFileObject($path, $mode);
        $data = $this->parameters->hasKey(static::BINARY_PARAMETER) ?
            base64_decode($this->data) :
            rawurldecode($this->data);

        $file->fwrite($data);

        return $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->mimetype;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function isBinaryData()
    {
        return $this->parameters->hasKey(static::BINARY_PARAMETER);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeParameters($parameters)
    {
        return $this->updateParameters($this->parameters->merge($parameters));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutParameters($parameters)
    {
        return $this->updateParameters($this->parameters->without($parameters));
    }

    /**
     * {@inheritdoc}
     */
    public function withParameters($parameters)
    {
        return $this->updateParameters($this->parameters->modify($parameters));
    }

    /**
     * Update the Parameter propery and return a new Instance of
     * the DataUri object if needed
     *
     * @param Interfaces\Parameters $parameters
     *
     * @return static
     */
    protected function updateParameters(Interfaces\Parameters $parameters)
    {
        if ($this->parameters->hasKey(static::BINARY_PARAMETER) !== $parameters->hasKey(static::BINARY_PARAMETER)) {
            throw new InvalidArgumentException('You can not modify the data binary encoding');
        }
        return $this->withProperty('parameters', $parameters);
    }

    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        if (empty($uri)) {
            return new static(new Scheme('data'), '', new Parameters(static::DEFAULT_PARAMETER), '');
        }

        return static::createFromComponents(static::parse($uri));
    }

    /**
     * Create a new instance from a file path
     *
     * @param string $path
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromPath($path)
    {
        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf('The specified file `%s` is not readable', $path));
        }

        $data = file_get_contents($path);
        $res = explode(';', (new \finfo(FILEINFO_MIME))->file($path), 2);
        $mimetype = array_shift($res);
        $parameters = new Parameters((string) array_pop($res));
        if ($parameters->hasKey(static::BINARY_PARAMETER) || 0 !== strpos($mimetype, 'text/')) {
            $parameters = $parameters->merge([static::BINARY_PARAMETER => null]);

            return new static(new Scheme('data'), $mimetype, $parameters, base64_encode($data));
        }

        return new static(new Scheme('data'), $mimetype, $parameters, rawurlencode($data));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components
     *
     * @return static
     */
    public static function createFromComponents(array $components)
    {
        if (!preg_match('|^(?<mediatype>.*)?,(?<data>.*)$|i', $components['path'], $matches)) {
            throw new InvalidArgumentException('The submitted uri is invalid');
        }

        $mimetype = '';
        $parameters = static::DEFAULT_PARAMETER;
        if (!empty($matches['mediatype'])) {
            $data = explode(';', $matches['mediatype'], 2);
            $mimetype = array_shift($data);
            $parameters = (string) array_pop($data);
        }

        return new static(new Scheme($components['scheme']), $mimetype, new Parameters($parameters), $matches['data']);
    }
}
