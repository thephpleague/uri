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
use League\Url\Util;

/**
* Value object representing a URL scheme component.
*
* @package League.url
* @since 1.0.0
*/
class Scheme extends AbstractComponent implements Interfaces\Scheme
{
    use Util\StandardPort;

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        if (! preg_match('/^[a-z][-a-z0-9+.]+$/i', $data)) {
            throw new InvalidArgumentException('The submitted data is invalid');
        }

        return strtolower($data);
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

        return $this->getStandardPortsFromScheme(array_pop($res));
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
