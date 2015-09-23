<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.url
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use Pdp\Parser;
use Pdp\PublicSuffixListManager;

/**
 * Value object representing a URI host component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait HostnameInfoTrait
{
    /**
     * Pdp Parser
     *
     * @var Parser
     */
    protected static $pdpParser;

    /**
     * Hostname public info
     *
     * @var array
     */
    protected $hostnameInfo = [
        'isPublicSuffixValid' => false,
        'publicSuffix' => null,
        'registerableDomain' => null,
        'subdomain' => null,
    ];

    /**
     * is the Hostname Info loaded
     *
     * @var bool
     */
    protected $hostnameInfoLoaded = false;

    /**
     * @inheritdoc
     */
    public function getPublicSuffix()
    {
        return $this->getHostnameInfo('publicSuffix');
    }

    /**
     * @inheritdoc
     */
    public function getRegisterableDomain()
    {
        return $this->getHostnameInfo('registerableDomain');
    }

    /**
     * @inheritdoc
     */
    public function getSubdomain()
    {
        return $this->getHostnameInfo('subdomain');
    }

    /**
     * @inheritdoc
     */
    public function isPublicSuffixValid()
    {
        return $this->getHostnameInfo('isPublicSuffixValid');
    }

    /**
     * Load the hostname info
     *
     * @param string $key hostname info key
     *
     * @return mixed
     */
    protected function getHostnameInfo($key)
    {
        $this->loadHostnameInfo();
        return $this->hostnameInfo[$key];
    }

    /**
     * parse and save the Hostname information from the Parser
     */
    protected function loadHostnameInfo()
    {
        if ($this->isIp() || $this->hostnameInfoLoaded) {
            return;
        }

        $host = $this->__toString();
        if ($this->isAbsolute()) {
            $host = mb_substr($host, 0, -1, 'UTF-8');
        }
        
        $this->hostnameInfo = $this->getPdpParser()->parseHost($host)->toArray();
        $this->hostnameInfo['isPublicSuffixValid'] = $this->getPdpParser()->isSuffixValid($host);
        $this->hostnameInfoLoaded = true;
    }

    /**
     * @inheritdoc
     */
    abstract public function __toString();

    /**
     * @inheritdoc
     */
    abstract public function isIp();

    /**
     * @inheritdoc
     */
    abstract public function isAbsolute();

    /**
     * Initialize and access the Parser object
     *
     * @return Parser
     */
    protected function getPdpParser()
    {
        if (!static::$pdpParser instanceof Parser) {
            static::$pdpParser = new Parser((new PublicSuffixListManager())->getList());
        }

        return static::$pdpParser;
    }
}
