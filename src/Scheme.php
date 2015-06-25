<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Url;

use InvalidArgumentException;

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
        $this->registry = !is_null($registry) ? $registry : new Services\SchemeRegistry();
        $data = $this->validateString($data);
        if (!empty($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemeRegistry()
    {
        return $this->registry;
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
        if (empty($this->registry->hasOffset($data))) {
            throw new InvalidArgumentException(sprintf(
                "the submitted scheme '%s' is no registered in the `SchemeRegistry` object",
                $data
            ));
        }

        return strtolower($data);
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
