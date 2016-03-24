<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.1
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Port as PortInterface;
use League\Uri\Types\ValidatorTrait;

/**
 * Value object representing a URI port component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Port extends AbstractComponent implements PortInterface
{
    use ValidatorTrait;

    /**
     * Validate Port data
     *
     * @param null|int $port
     *
     * @throws InvalidArgumentException if the submitted port is invalid
     *
     * @return null|int
     */
    protected function validate($port)
    {
        if ('' === $port) {
            throw new InvalidArgumentException(
                'Expected port to be a int or null; received an empty string'
            );
        }

        return $this->validatePort($port);
    }

    /**
     * Returns the instance string representation
     * with its optional URI delimiters
     *
     * @return string
     */
    public function getUriComponent()
    {
        return null === $this->data ? '' : PortInterface::DELIMITER.$this->data;
    }

    /**
     * Return an integer representation of the Port component
     *
     * @return null|int
     */
    public function toInt()
    {
        return $this->data;
    }

    /**
     * Initialize the Port data
     *
     * @param null|int $data
     */
    protected function init($data)
    {
        $this->data = $this->validate($data);
    }
}
