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
    use Utilities\StandardPort;

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        if (! static::isSupported($data)) {
            throw new InvalidArgumentException('Unsupported scheme');
        }

        return strtolower($data);
    }

    /**
     * Tell wether the submitted scheme is implemented in the package
     *
     * @param  string  $scheme
     *
     * @throws InvalidArgumentException If the submitted data is not implemented
     *
     * @return boolean
     */
    public static function isSupported($scheme)
    {
        if (! preg_match('/^[a-z][-a-z0-9+.]+$/i', $scheme)) {
            throw new InvalidArgumentException('The submitted data is invalid');
        }
        $res = explode('+', $scheme);
        $real_scheme = strtolower(array_pop($res));
        return empty($real_scheme) || isset(static::$standardPorts[$real_scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStandardPorts()
    {
        $res = [];
        if (! $this->isEmpty()) {
            $res = explode('+', $this->data);
        }

        return array_map(function ($value) {
            return new Port($value);
        }, $this->getAssociatedData(static::$standardPorts, array_pop($res)));
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
