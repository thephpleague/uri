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
* Value object representing a URL port component.
*
* @package League.url
* @since 1.0.0
*/
class Port extends AbstractComponent implements Interfaces\Port
{
    use Util\StandardPort;

    /**
     * New Instance
     *
     * @param int $data
     */
    public function __construct($data = null)
    {
        if (! is_null($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * Validate the port
     *
     * @param int $data
     */
    protected function validate($data)
    {
        $data = filter_var($data, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if (false === $data) {
            throw new InvalidArgumentException('The submitted port is invalid');
        }

        return $data;
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
        return ':'.$data;
    }

    /**
     * {@inheritdoc}
     */
    public function toInt()
    {
        return $this->data;
    }

    /**
     * Tells whether the given scheme uses the current port as his standard
     *
     * @param string $scheme a valid scheme string OR a stringable object
     *
     * @return bool
     */
    protected function isStandardFor($scheme)
    {
        $scheme = explode('+', $scheme);
        $res    = new Scheme(array_pop($scheme));
        if ($this->isEmpty()) {
            return true;
        }

        $res = array_filter($this->getStandardSchemes(), function ($value) use ($res) {
            return $res->sameValueAs($value);
        });

        return ! empty($res);
    }

    /**
     * {@inheritdoc}
     */
    public function getStandardSchemes()
    {
        return $this->getStandardSchemesFromPort($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function format($scheme)
    {
        if ($this->isStandardFor($scheme)) {
            return '';
        }

        return $this->getUriComponent();
    }
}
