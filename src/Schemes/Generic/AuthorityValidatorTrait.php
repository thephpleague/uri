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
namespace League\Uri\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Components\Host;
use League\Uri\Components\Scheme;

/**
 * A trait to validate the host in a URI context
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait AuthorityValidatorTrait
{
    /**
     * {@inheritdoc}
     */
    abstract public function getSchemeSpecificPart();

    /**
     * {@inheritdoc}
     */
    abstract public function getScheme();

    /**
     * {@inheritdoc}
     */
    abstract public function getHost();

    /**
     * Tell whether the Auth URI is valid
     *
     * @throws InvalidArgumentException If the Scheme is not supported
     *
     * @return bool
     */
    protected function isAuthorityValid()
    {
        $pos = strpos($this->getSchemeSpecificPart(), '//');
        $scheme = $this->getScheme();
        if (!empty($scheme) && 0 !== $pos) {
            return false;
        }

        $host = $this->getHost();
        return !(empty($host) && 0 === $pos);
    }
}
