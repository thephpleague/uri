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
namespace League\Uri;

use InvalidArgumentException;

/**
 * A class to manipulate URI and URI components output
 *
 * @package League.uri
 * @since   4.0.0
 */
class Formatter
{
    /**
     * Constants for host formatting
     */
    const HOST_AS_UNICODE = 1;
    const HOST_AS_ASCII   = 2;

    /*
     * A trait to format a path in a URI string
     */
    use Components\PathFormatterTrait;

    /**
     * host encoding property
     *
     * @var int
     */
    protected $hostEncoding = self::HOST_AS_UNICODE;

    /**
     * query encoding property
     *
     * @var int
     */
    protected $queryEncoding = PHP_QUERY_RFC3986;

    /**
     * query separator property
     *
     * @var string
     */
    protected $querySeparator = '&';

    /**
     * Host encoding setter
     *
     * @param int $encode a predefined constant value
     */
    public function setHostEncoding($encode)
    {
        if (!in_array($encode, [self::HOST_AS_UNICODE, self::HOST_AS_ASCII])) {
            throw new InvalidArgumentException('Unknown Host encoding rule');
        }
        $this->hostEncoding = $encode;
    }

    /**
     * Host encoding getter
     *
     * @return int
     */
    public function getHostEncoding()
    {
        return $this->hostEncoding;
    }

    /**
     * Query encoding setter
     *
     * @param int $encode a predefined constant value
     */
    public function setQueryEncoding($encode)
    {
        if (!in_array($encode, [PHP_QUERY_RFC3986, PHP_QUERY_RFC1738])) {
            throw new InvalidArgumentException('Unknown Query encoding rule');
        }
        $this->queryEncoding = $encode;
    }

    /**
     * Query encoding getter
     *
     * @return int
     */
    public function getQueryEncoding()
    {
        return $this->queryEncoding;
    }

    /**
     * Query separator setter
     *
     * @param string $separator
     */
    public function setQuerySeparator($separator)
    {
        $separator = filter_var($separator, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

        $this->querySeparator = trim($separator);
    }

    /**
     * Query separator getter
     *
     * @return string
     */
    public function getQuerySeparator()
    {
        return $this->querySeparator;
    }

    /**
     * Format an object according to the formatter properties
     *
     * @param Interfaces\Schemes\Uri|Interfaces\UriPart $input
     *
     * @return string
     */
    public function format($input)
    {
        if ($input instanceof Interfaces\UriPart) {
            return $this->formatUriPart($input);
        }

        if ($input instanceof Interfaces\Schemes\Uri) {
            return $this->formatUri($input);
        }

        throw new InvalidArgumentException(sprintf(
            'input must be an Uri or an UriPart implemented object; received "%s"',
            is_object($input) ? get_class($input) : gettype($input)
        ));
    }

    /**
     * Format a Interfaces\UriPart implemented object according to the Formatter properties
     *
     * @param Interfaces\UriPart $part
     *
     * @return string
     */
    protected function formatUriPart(Interfaces\UriPart $part)
    {
        if ($part instanceof Interfaces\Query) {
            return Components\Query::build($part->toArray(), $this->querySeparator, $this->queryEncoding);
        }

        if ($part instanceof Interfaces\Host) {
            return $this->formatHost($part);
        }

        return $part->__toString();
    }

    /**
     * Format a Interfaces\Host according to the Formatter properties
     *
     * @param Interfaces\Host $host
     *
     * @return string
     */
    protected function formatHost(Interfaces\Host $host)
    {
        if (self::HOST_AS_ASCII == $this->hostEncoding) {
            return $host->toAscii()->__toString();
        }

        return $host->toUnicode()->__toString();
    }

    /**
     * Format a Interfaces\Schemes\Uri according to the Formatter properties
     *
     * @param Interfaces\Schemes\Uri $uri
     *
     * @return string
     */
    protected function formatUri(Interfaces\Schemes\Uri $uri)
    {
        if (!$uri instanceof Interfaces\Schemes\HierarchicalUri) {
            return $uri->__toString();
        }

        $query = $this->formatUriPart($uri->query);
        if (!empty($query)) {
            $query = '?'.$query;
        }

        $auth = $this->formatAuthority($uri);

        return $uri->scheme->getUriComponent().$auth
            .$this->formatPath($uri->path, !empty($auth)).$query
            .$uri->fragment->getUriComponent();
    }

    /**
     * Format a URI authority according to the Formatter properties
     *
     * @param Interfaces\Schemes\HierarchicalUri $uri
     *
     * @return string
     */
    protected function formatAuthority(Interfaces\Schemes\HierarchicalUri $uri)
    {
        if ('' == $uri->getHost()) {
            return '';
        }

        $port = $uri->port->getUriComponent();
        if ($uri->hasStandardPort()) {
            $port = '';
        }

        return '//'.$uri->userInfo->getUriComponent().$this->formatHost($uri->host).$port;
    }
}
