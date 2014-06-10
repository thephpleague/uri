<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use League\Url\Interfaces\UrlInterface;
use League\Url\Interfaces\QueryInterface;
use League\Url\Interfaces\SegmentInterface;
use League\Url\Interfaces\ComponentInterface;
use League\Url\Interfaces\EncodingInterface;

/**
 * A abstract class to manipulate URLs
 *
 * @package League.url
 */
abstract class AbstractUrl implements UrlInterface, EncodingInterface
{
    /**
    * Scheme
    *
    * @var{@link ComponentInterface}  Object
    */
    protected $scheme;

    /**
    * User
    *
    * @var {@link ComponentInterface} Object
    */
    protected $user;

    /**
    * Pass
    *
    * @var {@link ComponentInterface} Object
    */
    protected $pass;

    /**
     * Host
     *
     * @var {@link SegmentInterface} Object
     */
    protected $host;

    /**
     * Port
     *
     *@var {@link ComponentInterface} Object
     */
    protected $port;

    /**
     * Path
     *
     * @var {@link SegmentInterface} Object
     */
    protected $path;

    /**
     * Query
     *
     * @var {@link QueryInterface} Object
     */
    protected $query;

    /**
     * Fragment
     *
     * @var {@link ComponentInterface} Object
     */
    protected $fragment;

    /**
     * The Constructor
     *
     * @param {@link ComponentInterface} $scheme   Url Scheme object
     * @param {@link ComponentInterface} $user     Url Component object
     * @param {@link ComponentInterface} $pass     Url Component object
     * @param {@link SegmentInterface}   $host     Url Host object
     * @param {@link ComponentInterface} $port     Url Port object
     * @param {@link SegmentInterface}   $path     Url Path object
     * @param {@link QueryInterface}     $query    Url Query object
     * @param {@link ComponentInterface} $fragment Url Component object
     */
    public function __construct(
        ComponentInterface $scheme,
        ComponentInterface $user,
        ComponentInterface $pass,
        SegmentInterface $host,
        ComponentInterface $port,
        SegmentInterface $path,
        QueryInterface $query,
        ComponentInterface $fragment
    ) {
        $this->scheme = $scheme;
        $this->user = $user;
        $this->pass = $pass;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getBaseUrl().$this->getRelativeUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeUrl()
    {
        $path = $this->path->getUriComponent();
        $query = $this->query->getUriComponent();
        $fragment = $this->fragment->getUriComponent();
        if ('' == $path) {
            $path = '/'.$path;
        }

        return $path.$query.$fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        $scheme = $this->scheme->getUriComponent();
        $user = $this->user->getUriComponent();
        $pass = $this->pass->getUriComponent();
        $host = $this->host->getUriComponent();
        $port = $this->port->getUriComponent();

        $user .= $pass;
        if ('' != $user) {
            $user .= '@';
        }

        if ('' != $host && '' == $scheme) {
            $scheme = '//';
        }

        return $scheme.$user.$host.$port;
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UrlInterface $url)
    {
        $this_url = $this->setEncodingType(PHP_QUERY_RFC1738)->__toString();
        $that_url = $url->setEncodingType(PHP_QUERY_RFC1738)->__toString();

        return  $this_url == $that_url;
    }

    /**
     * {@inheritdoc}
     */
    public function getEncodingType()
    {
        return $this->query->getEncodingType();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function setEncodingType($encoding_type);
}
