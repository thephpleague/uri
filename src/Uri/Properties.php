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
namespace League\Uri\Uri;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * a Trait to access URI properties methods
 *
 * @package League.url
 * @since   1.0.0
 *
 */
trait Properties
{
    /**
     * A Factory trait to fetch info from Server environment variables
     */
    use ServerInfo;

    /**
     * a Factory to create new URI instances
     */
    use Factory;

    /**
     * partially modifying an URL object
     */
    use PartialModifier;

    /**
     * {@inheritdoc}
     */
    abstract public function __toString();

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return static::parse($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function hasStandardPort()
    {
        if ($this->scheme->isEmpty()) {
            return false;
        }

        if ($this->port->isEmpty()) {
            return true;
        }

        return $this->scheme->getSchemeRegistry()->getPort($this->scheme)->sameValueAs($this->port);
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return !$this->scheme->isEmpty() && !$this->host->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UriInterface $url)
    {
        if (!$url instanceof League\Uri\Uri) {
            try {
                $url = static::createFromString($url->__toString(), $this->scheme->getSchemeRegistry());
            } catch (InvalidArgumentException $e) {
                return false;
            }
        }

        return $url->toAscii()->ksortQuery()->__toString() === $this->toAscii()->ksortQuery()->__toString();
    }
}
