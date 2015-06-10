<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url;

use InvalidArgumentException;
use League\Url\Interfaces;
use League\Url\Utilities;

/**
 * Value object representing a URL scheme component.
 *
 * @package League.url
 * @since 1.0.0
 */
class Scheme extends Component implements Interfaces\Scheme
{
    use Utilities\RegisteredSchemes;

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        if (! static::isRegistered($data)) {
            throw new InvalidArgumentException(sprintf(
                "the submitted scheme '%s' is no registered you should use `Scheme::register` first",
                $data
            ));
        }

        return strtolower($data);
    }

    /**
     * Tell wether the submitted scheme is implemented in the package
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the submitted scheme is invalid
     *
     * @return boolean
     */
    public static function isRegistered($scheme)
    {
        return isset(static::$registeredSchemes[static::formatScheme($scheme)]);
    }

    /**
     * Validate Scheme syntax according to RFC3986
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the submitted scheme is invalid
     *
     * @return string
     */
    protected static function formatScheme($scheme)
    {
        if (! filter_var($scheme, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-z][-a-z0-9+.]+$/i']])) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $scheme));
        }

        return strtolower($scheme);
    }

    /**
     * Register a new Scheme or add standard Port to an
     * already registered Scheme
     *
     * @param  string   $scheme
     * @param  int|null $port
     *
     * @throws InvalidArgumentException If the submitted port is invalid
     */
    public static function register($scheme, $port = null)
    {
        $scheme = static::formatScheme($scheme);
        static::assertValidScheme($scheme);
        if (! isset(static::$registeredSchemes[$scheme])) {
            static::$registeredSchemes[$scheme] = [];
        }
        if (empty($port)) {
            return;
        }
        if (! filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]])) {
            throw new InvalidArgumentException('The submitted port is invalid');
        };
        static::$registeredSchemes[$scheme][] = $port;
        sort(static::$registeredSchemes[$scheme]);
    }

    /**
     * Restrict action on default Schemes
     *
     * @param string $scheme
     *
     * @throws InvalidArgumentException If the scheme is part
     *                                     of the defaults list of registered scheme
     */
    protected static function assertValidScheme($scheme)
    {
        if (in_array($scheme, ['http', 'https', 'ws', 'wss', 'ftp', 'ftps', 'file'])) {
            throw new InvalidArgumentException(sprintf("The submitted scheme '%s' is already defined", $scheme));
        }
    }

    public static function unRegister($scheme)
    {
        $scheme = static::formatScheme($scheme);
        static::assertValidScheme($scheme);
        unset(static::$registeredSchemes[$scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStandardPorts()
    {
        $res  = [];
        if (isset(static::$registeredSchemes[$this->data])) {
            $res = static::$registeredSchemes[$this->data];
        }
        sort($res);

        return array_map(function ($value) {
            return new Port($value);
        }, $res);
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort($port)
    {
        if (! $port instanceof Interfaces\Port) {
            $port = new Port($port);
        }

        if ($port->isEmpty()) {
            return true;
        }

        $res = array_filter($this->getStandardPorts(), function ($value) use ($port) {
            return $port->sameValueAs($value);
        });

        return ! empty($res);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $data = $this->__toString();
        if (empty($data)) {
            return $data;
        }
        return $data.':';
    }
}
