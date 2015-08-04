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
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Components\Port as PortInterface;

/**
 * Value object representing a URI port component.
 *
 * @package League.uri
 * @since   1.0.0
 */
trait PortValidatorTrait
{
    /**
     * {@inheritdoc}
     */
    protected function validatePort($port)
    {
        $res = filter_var($port, FILTER_VALIDATE_INT, ['options' => [
            'min_range' => PortInterface::MINIMUM,
            'max_range' => PortInterface::MAXIMUM,
        ]]);
        if (false === $res || is_bool($port)) {
            throw new InvalidArgumentException('The submitted port is invalid');
        }

        return $res;
    }
}
