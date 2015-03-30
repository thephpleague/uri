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
use League\Url\Interfaces\Host as HostInterface;
use League\Url\Interfaces\Query as QueryInterface;
use League\Url\Interfaces\Url as UrlInterface;
use League\Url\Util;

/**
* A class to manipulate an URL as a Value Object
*
* @package League.url
* @since 4.0.0
*/
class Formatter
{

    const HOST_ASCII = 1;

    const HOST_UNICODE = 2;

    const QUERY_RFC1738 = \PHP_QUERY_RFC1738;

    const QUERY_RFC3986 = \PHP_QUERY_RFC3986;

    /**
     * host encoding property
     *
     * @var int
     */
    protected $hostEncoding = self::HOST_UNICODE;

    /**
     * query encoding property
     *
     * @var int
     */
    protected $queryEncoding = self::QUERY_RFC3986;

    /**
     * query separator property
     *
     * @var string
     */
    protected $querySeparator = '&';

    /**
     * Trait to validate string
     */
    use Util\StringValidator;

    /**
     * Host encoding setter
     *
     * @param int $encode a predefined constant value
     */
    public function setHostEncoding($encode)
    {
        if (! in_array($encode, [self::HOST_UNICODE, self::HOST_ASCII])) {
            throw new InvalidArgumentException('Unknown Host encoding set');
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
        if (! in_array($encode, [self::QUERY_RFC3986, self::QUERY_RFC1738])) {
            throw new InvalidArgumentException('Unknown Query encoding set');
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
        $separator = $this->validateString($separator);
        if (! empty($separator)) {
            $this->querySeparator = $separator;
        }
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
     * @param  mixed $args
     *
     * @throws \InvalidArgumentException if the given $args can not be formatted
     *
     * @return string
     */
    public function format($args)
    {
        if ($args instanceof QueryInterface) {
            return $this->formatQuery($args);
        }

        if ($args instanceof HostInterface) {
            return $this->formatHost($args);
        }

        if ($args instanceof UrlInterface) {
            return $this->formatUrl($args);
        }

        throw new InvalidArgumentException('Unknown object to be formatted');
    }

    /**
     * Format a League\Url\Interfaces\Query
     *
     * @param Query $query
     *
     * @return string
     */
    public function formatQuery(QueryInterface $query)
    {
        return http_build_query($query->toArray(), null, $this->querySeparator, $this->queryEncoding);
    }

    /**
     * Format a League\Url\Interfaces\Host
     *
     * @param Host $host
     *
     * @return string
     */
    public function formatHost(HostInterface $host)
    {
        if (self::HOST_ASCII == $this->hostEncoding) {
            return $host->toAscii();
        }
        return $host->toUnicode();
    }

    /**
     * Format a League\Url\Interfaces\Url
     *
     * @param Url $url
     *
     * @return string
     */
    public function formatUrl(UrlInterface $url)
    {
        $host  = $url->getHost();
        $query = $url->getQuery();
        $str   = $url->getScheme()->getUriComponent();
        if (count($host)) {
            $userinfo = $url->getUserInfo();
            if (! empty($userinfo)) {
                $userinfo .= '@';
            }
            $str .= '//'.$userinfo.$this->formatHost($host);
            if (! $url->hasStandadPort()) {
                $str .= $url->getPort()->getUriComponent();
            }
        }

        $str .= $url->getPath()->getUriComponent();
        if (count($query)) {
            $str .= '?'.$this->formatQuery($query);
        }

        return $str.$url->getFragment()->getUriComponent();
    }
}
