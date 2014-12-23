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

use Countable;
use IteratorAggregate;
use League\Url\Interfaces\HostInterface;
use LogicException;
use RuntimeException;
use True\Punycode;

/**
 *  A class to manipulate URL Host component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Host extends AbstractSegment implements
    Countable,
    HostInterface,
    IteratorAggregate
{
    /**
     * {@inheritdoc}
     */
    protected $delimiter = '.';

    protected $host_as_ipv6 = false;

    protected $host_as_ipv4 = false;

    /**
     * Punycode Algorithm Object
     * @var \True\Punycode
     */
    protected $punycode;

    /**
     * Environment Internal encoding
     * @var mixed
     */
    protected $encoding;

    /**
     * Alter the Environment Internal Encoding if it is not utf-8
     *
     * @return void
     */
    protected function saveInternalEncoding()
    {
        $this->encoding = mb_internal_encoding();
        if (stripos($this->encoding, 'utf-8') === false) {
            mb_internal_encoding('utf-8');
        }
    }

    /**
     * Restore the Environment Internal Encoding
     *
     * @return void
     */
    protected function restoreInternalEncoding()
    {
        mb_internal_encoding($this->encoding);
    }

    /**
     * {@inheritdoc}
     */
    public function __construct($data = null)
    {
        $this->punycode = new Punycode();
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        $this->setHostAsIp($data);
        if ($this->isIp()) {
            return;
        }

        $this->data = array_filter($this->validate($data), function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $res = [];
        foreach (array_values($this->data) as $value) {
            $res[] = $this->punycode->decode($value);
        }
        if (! $res) {
            return null;
        }

        return implode($this->delimiter, $res);
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        $this->saveInternalEncoding();
        $res = $this->punycode->encode($this->__toString());
        $this->restoreInternalEncoding();

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function toUnicode()
    {
        return $this->__toString();
    }

    /**
     * Validate Host label length
     *
     * @param  array $data Host labels
     *
     * @return boolean
     */
    protected function isValidHostLength(array $data)
    {
        $res = array_filter($data, function ($label) {
            return mb_strlen($label) > 63;
        });

        return 0 == count($res);
    }

    /**
     * Validated the Host Label Pattern
     *
     * @param  array $data Host segment
     *
     * @return boolean
     */
    protected function isValidHostPattern(array $data)
    {
        $data = explode(
            $this->delimiter,
            $this->punycode->encode(implode($this->delimiter, $data))
        );

        $res = preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT);

        return 0 == count($res);
    }

    protected function isValidHostLabels(array $data = [])
    {
        $labels       = array_merge($this->data, $data);
        $count_labels = count($labels);

        return $count_labels > 0 && $count_labels < 127 && 255 > strlen(implode($this->delimiter, $labels));
    }

    /**
     * Validate Host data before insertion into a URL host component
     *
     * @param mixed $data the data to insert
     *
     * @return array
     *
     * @throws RuntimeException If the added is invalid
     */
    protected function validate($data)
    {
        $data = $this->validateSegment($data);
        if (! $data) {
            return $data;
        }

        $this->saveInternalEncoding();
        if (! $this->isValidHostLength($data)) {
            $this->restoreInternalEncoding();
            throw new RuntimeException('Invalid hostname, check its length');
        } elseif (! $this->isValidHostPattern($data)) {
            $this->restoreInternalEncoding();
            throw new RuntimeException('Invalid host label, check its content');
        } elseif (! $this->isValidHostLabels($data)) {
            $this->restoreInternalEncoding();
            throw new RuntimeException('Invalid host label counts, check its count');
        }

        $data = $this->sanitizeValue($data);
        $data = explode(
            $this->delimiter,
            $this->punycode->decode(implode($this->delimiter, $data))
        );
        $this->restoreInternalEncoding();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isIpv4()
    {
        return $this->host_as_ipv4;
    }

    /**
     * {@inheritdoc}
     */
    public function isIpv6()
    {
        return $this->host_as_ipv6;
    }

    /**
     * {@inheritdoc}
     */
    public function isIp()
    {
        return $this->host_as_ipv6 || $this->host_as_ipv4;
    }

    /**
     * Set the Host as a IP Address
     *
     * @param string $str the raw Host string
     */
    protected function setHostAsIp($str)
    {
        $this->host_as_ipv4 = false;
        $this->host_as_ipv6 = false;
        if (! self::isStringable($str)) {
            return;
        }

        $str = (string) $str;
        $str = trim($str);
        if ('[' == $str[0] && ']' == $str[strlen($str)-1]) {
            $str = substr($str, 1, -1);
        }

        if (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->host_as_ipv4 = true;
            $this->host_as_ipv6 = false;
            $this->data = [$str];
        } elseif (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->host_as_ipv4 = false;
            $this->host_as_ipv6 = true;
            $this->data = [$str];
        }
    }

    /**
     * Assert the nature of the Host IP or Not
     *
     * @throws LogicException If the Host is a valid IP address
     */
    protected function assertHostAsIp()
    {
        if ($this->isIp()) {
            throw new LogicException('You can not modify a IP based host');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function append($data, $whence = null, $whence_index = null)
    {
        $this->assertHostAsIp();

        return parent::append($data, $whence, $whence_index);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($data, $whence = null, $whence_index = null)
    {
        $this->assertHostAsIp();

        return parent::prepend($data, $whence, $whence_index);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $str = $this->__toString();
        if ($this->host_as_ipv6) {
            return '['.$str.']';
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }
}
