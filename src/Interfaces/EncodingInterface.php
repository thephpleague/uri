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
namespace League\Url\Interfaces;

interface EncodingInterface
{
    /**
     * Set the Query String encoding type (see {@link http_build_query})
     *
     * @param integer $encoding_type
     *
     * @return void
     */
    public function setEncodingType($encoding_type);

    /**
     * return the current Encoding type value
     *
     * @return integer
     */
    public function getEncodingType();
}

//@codeCoverageIgnoreStart
if (! defined('PHP_QUERY_RFC1738')) {
    define('PHP_QUERY_RFC1738', 1);
    define('PHP_QUERY_RFC3986', 2);
}
//@codeCoverageIgnoreEnd
