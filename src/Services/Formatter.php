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
namespace League\Url\Services;

use InvalidArgumentException;
use League\Url\Interfaces;
use League\Url\Url;

/**
 * A class to manipulate an URL output
 *
 * @package League.url
 * @since 4.0.0
 */
class Formatter
{
    const HOST_AS_UNICODE = 1;

    const HOST_AS_ASCII   = 2;

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
        if (! in_array($encode, [self::HOST_AS_UNICODE, self::HOST_AS_ASCII])) {
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
     * @param int $encode  a predefined constant value
     */
    public function setQueryEncoding($encode)
    {
        if (! in_array($encode, [PHP_QUERY_RFC3986, PHP_QUERY_RFC1738])) {
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
     * @param Interfaces\UrlPart|Url|string $input
     *
     * @return string
     */
    public function format($input)
    {
        if ($input instanceof Url) {
            return $this->formatUrl($input);
        }

        if ($input instanceof Interfaces\UrlPart) {
            return $this->formatComponent($input);
        }

        return $this->formatUrl(Url::createFromUrl($input));
    }

    /**
     * Format a League\Url\Interfaces\UrlPart according to the Formatter properties
     *
     * @param Interfaces\UrlPart $part
     *
     * @return string
     */
    protected function formatComponent(Interfaces\UrlPart $part)
    {
        if ($part instanceof Interfaces\Query) {
            return $part->format($this->querySeparator, $this->queryEncoding);
        }

        if ($part instanceof Interfaces\Host) {
            return $this->formatHost($part);
        }

        return $part->__toString();
    }

    /**
     * Format a League\Url\Interfaces\Host according to the Formatter properties
     *
     * @param Interfaces\Host $host
     *
     * @return string
     */
    protected function formatHost(Interfaces\Host $host)
    {
        if (self::HOST_AS_ASCII == $this->hostEncoding) {
            return $host->toAscii();
        }

        return $host->__toString();
    }

    /**
     * Format a Url according to the Formatter properties
     *
     * @param Url $url
     *
     * @return string
     */
    protected function formatUrl(Url $url)
    {
        $query = $url->query->format($this->querySeparator, $this->queryEncoding);
        if (! empty($query)) {
            $query = '?'.$query;
        }

        $auth = $this->formatAuthority($url);

        return $url->scheme->getUriComponent()
            .$auth
            .$url->path->format($auth)
            .$query
            .$url->fragment->getUriComponent();
    }

    /**
     * Format a URL authority according to the Formatter properties
     *
     * @param Url $url
     *
     * @return string
     */
    protected function formatAuthority(Url $url)
    {
        if ('' == $url->getHost()) {
            return '';
        }

        return '//'.$url->userinfo->getUriComponent()
            .$this->formatHost($url->host)
            .$url->port->getUriComponent();
    }
}
