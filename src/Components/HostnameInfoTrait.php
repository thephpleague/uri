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
namespace League\Uri\Components;

use Pdp;

/**
 * Value object representing a URI host component.
 *
 * @package League.uri
 * @since   4.0.0
 */
trait HostnameInfoTrait
{
    /**
     * Pdp Parser
     *
     * @var Pdp\Parser
     */
    protected static $parser;

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
     * Initialize and access a Pdp\Parser object
     *
     * @return Pdp\Parser
     */
    protected function getHostnameParser()
    {
        if (!static::$parser instanceof Pdp\Parser) {
            static::$parser = $this->newHostnameParser();
        }

        return static::$parser;
    }

    /**
     * Pdp Factory
     *
     * @return Pdp\Parser
     */
    protected function newHostnameParser()
    {
        return new Pdp\Parser((new Pdp\PublicSuffixListManager())->getList());
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicSuffix()
    {
        return $this->getHostnameInfo('publicSuffix');
    }

    /**
     * {@inheritdoc}
     */
    public function getRegisterableDomain()
    {
        return $this->getHostnameInfo('registerableDomain');
    }

    /**
     * {@inheritdoc}
     */
    public function getSubdomain()
    {
        return $this->getHostnameInfo('subdomain');
    }

    /**
     * {@inheritdoc}
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

    protected function loadHostnameInfo()
    {
        if ($this->isIp() || $this->hostnameInfoLoaded) {
            return;
        }

        $host = $this->__toString();
        if ($this->isAbsolute()) {
            $host = mb_substr($host, 0, -1, 'UTF-8');
        }

        $info = $this->getHostnameParser()->parseHost($host);
        $this->hostnameInfo['subdomain'] = $info->subdomain;
        $this->hostnameInfo['registerableDomain'] = $info->registerableDomain;
        $this->hostnameInfo['publicSuffix'] = $info->publicSuffix;
        $this->hostnameInfo['isPublicSuffixValid'] = $this->getHostnameParser()->isSuffixValid($host);
        $this->hostnameInfoLoaded = true;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function isIp();

    /**
     * {@inheritdoc}
     */
    abstract public function isAbsolute();
}
