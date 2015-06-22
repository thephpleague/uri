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
        $data = strtolower($data);
        if (empty($this->registry->hasOffset($data))) {
            throw new InvalidArgumentException(sprintf(
                "the submitted scheme '%s' is no registered in the `SchemeRegistry` object",
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
