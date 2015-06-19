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
use League\Url\Services;

/**
 * Value object representing a URL scheme component.
 *
 * @package League.url
 * @since 1.0.0
 */
class Scheme extends AbstractComponent implements Interfaces\Scheme
{
    /**
     * Scheme registry object
     *
     * @var Interfaces\SchemeRegistry
     */
    protected $registry;

    /**
     * new instance
     *
     * @param string                         $data the component value
     * @param Interfaces\SchemeRegistry|null $registry
     *
     */
    public function __construct($data = null, Interfaces\SchemeRegistry $registry = null)
    {
        $this->setSchemeRegistry($registry);
        $data = $this->validateString($data);
        if (! empty($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * Set the SchemeRegistry object
     *
     * @param Interfaces\SchemeRegistry|null $registry
     */
    protected function setSchemeRegistry(Interfaces\SchemeRegistry $registry = null)
    {
        if (is_null($registry)) {
            $this->registry = new Services\SchemeRegistry();
            return;
        }

        $this->registry = clone $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemeRegistry()
    {
        return clone $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($value)
    {
        if ($value == $this->__toString()) {
            return $this;
        }

        return new static($value, $this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = strtolower($data);
        if (! $this->registry->has($data)) {
            throw new InvalidArgumentException(sprintf(
                "the submitted scheme '%s' is no registered you should use `SchemeRegistry::add` first",
                $data
            ));
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
        return $data.':';
    }
}
