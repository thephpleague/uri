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
use League\Uri\Interfaces;
use League\Uri\Parameters;
use League\Uri\Scheme;
use SplFileObject;
use finfo;

/**
 * Value object representing HTTP and HTTPS Uri.
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
class Data implements Interfaces\Schemes\Uri
{
    const DEFAULT_MIMETYPE = 'text/plain';

    const DEFAULT_PARAMETER = 'charset=us-ascii';

    const BINARY_PARAMETER = 'base64';

    /*
     * URI complementary methods
     */
    use Uri\Opaque;

    /**
     * The URI scheme
     *
     * @var Interfaces\Scheme
     */
    protected $scheme;

    /**
     * The URI mediatype parameters
     *
     * @var Interfaces\Parameters
     */
    protected $parameters;

    /**
     * A new Data URI instance
     * @param Interfaces\Scheme     $scheme
     * @param string                $mimeType   The Data URI associated MimeType
     * @param Interfaces\Parameters $parameters The parameter associated with the MediaType
     * @param string                $data       The encoded data
     */
    public function __construct(
        Interfaces\Scheme $scheme,
        $mimeType,
        Interfaces\Parameters $parameters,
        $data
    ) {
        $this->mimeType = $this->setMimeType($mimeType);
        $this->parameters = $this->filterParameters($parameters);
        if ('data' !== $scheme->__toString()) {
            throw new InvalidArgumentException('Invalid Scheme');
        }
        if ($this->parameters->hasKey('base64') && !base64_decode($data, true)) {
            throw new InvalidArgumentException(sprintf('The submitted data is invalid: `%s`', $data));
        }
        $this->scheme = $scheme;
        $this->data = $data;
    }

    /**
     * Filter the submitted Parameter object
     *
     * @param  Interfaces\Parameters $parameters
     *
     * @throws InvalidArgumentException If the object contain invalid data
     *
     * @return Interfaces\Parameters
     */
    protected function filterParameters(Interfaces\Parameters $parameters)
    {
        foreach ($parameters as $key => $value) {
            if ($key !== static::BINARY_PARAMETER && null === $value) {
                throw new InvalidArgumentException('The mediatype contain invalid parameters');
            }
        }

        return $parameters;
    }

    protected function setMimeType($mimeType)
    {
        if (empty($mimeType)) {
            return static::DEFAULT_MIMETYPE;
        }

        if (!preg_match(',^[a-z-\/+]+$,i', $mimeType)) {
            throw new InvalidArgumentException(sprintf('invalid mimetype, `%s`', $mimeType));
        }

        return $mimeType;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->$name;
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
    public function getScheme()
    {
        return $this->scheme->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemeSpecificPart()
    {
        return $this->mimeType . $this->parameters->getUriComponent() . ',' . $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function save($path, $mode = 'w')
    {
        if (!$path instanceof SplFileObject) {
            $path = new SplFileObject($path, $mode);
        }

        if ($this->parameters->hasKey(static::BINARY_PARAMETER)) {
            $path->fwrite(base64_decode($this->data));

            return $path;
        }

        $path->fwrite(rawurldecode($this->data));

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'scheme' => $this->scheme->__toString(),
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => $this->getSchemeSpecificPart(),
            'query' => null,
            'fragment' => null,
        ];
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
        $parameters = $this->filterParameters($parameters);
        if ($this->parameters->sameValueAs($parameters)) {
            return $this;
        }
        $clone = clone $this;
        $clone->parameters = $parameters;

        return $clone;
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
        $mimetype = '';
        $parameters = static::DEFAULT_PARAMETER;
        if (empty($uri)) {
            return new static(new Scheme('data'), $mimetype, new Parameters($parameters), '');
        }

        if (!preg_match('|^data:(?<mediatype>.*)?,(?<data>.*)$|i', $uri, $matches)) {
            throw new InvalidArgumentException(sprintf('The submitted string is invalid: `%s`', $uri));
        }

        if (!empty($matches['mediatype'])) {
            $data = explode(';', $matches['mediatype'], 2);
            $mimetype = (string) array_shift($data);
            $parameters = (string) array_pop($data);
        }

        return new static(new Scheme('data'), $mimetype, new Parameters($parameters), $matches['data']);
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
        $res = explode(';', (new finfo(FILEINFO_MIME))->file($path), 2);
        $mimeType = array_shift($res);
        $parameters = new Parameters((string) array_pop($res));
        if (strpos($mimeType, 'text/') !== 0 || $parameters->hasKey(static::BINARY_PARAMETER)) {
            $param = $parameters->merge([static::BINARY_PARAMETER => null]);

            return new static(new Scheme('data'), $mimeType, $param, base64_encode($data));
        }

        return new static(new Scheme('data'), $mimeType, $parameters, rawurlencode($data));
    }
}
