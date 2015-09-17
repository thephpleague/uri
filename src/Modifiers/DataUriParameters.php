<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Modifiers;

use League\Uri\Components\DataPath;

/**
 * Data Uri Paramaters Modifier
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class DataUriParameters extends AbstractPathModifier
{
    /**
     * A DataPath object
     *
     * @var string
     */
    protected $parameters;

    /**
     * New instance
     *
     * @param string $parameters the data to be used
     *
     */
    public function __construct($parameters)
    {
        $this->parameters = $this->filterParamaters($parameters);
    }

    /**
     * Return a new modifier with a new parameters string to update
     *
     * @param string $parameters the data to be used
     *
     * @return $this
     */
    public function withParameters($parameters)
    {
        return new static($parameters);
    }

    /**
     * Filter and validate the parameters
     *
     * @param string $parameters the data to be used
     *
     * @throws InvalidArgumentException if the value is invalid
     *
     * @return string
     */
    protected function filterParamaters($parameters)
    {
        return (new DataPath('text/plain;charset=us-ascii,'))->withParameters($parameters)->getParameters();
    }

    /**
     * @inheritdoc
     */
    protected function modify($str)
    {
        return (new DataPath($str))->withParameters($this->parameters)->__toString();
    }
}
