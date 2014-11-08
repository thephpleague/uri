<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.2.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Components;

use RuntimeException;
use True\Punycode;

/**
 *  A class to manipulate URL Host component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Host extends AbstractSegment implements HostInterface
{
    /**
     * {@inheritdoc}
     */
    protected $delimiter = '.';

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
    public function get()
    {
        $res = array();
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

    protected function isValidHostLength(array $data)
    {
        $res = array_filter($data, function ($label) {
            return mb_strlen($label) > 63;
        });

        return 0 == count($res);
    }

    protected function isValidHostPattern(array $data)
    {
        $data = explode(
            $this->delimiter,
            $this->punycode->encode(implode($this->delimiter, $data))
        );

        $res = preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT);

        return 0 == count($res);
    }

    protected function isValidHostLabels(array $data = array())
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
}
