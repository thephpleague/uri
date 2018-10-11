---
layout: default
title: PSL Manager
redirect_from:
    - /publicsuffix/manager/
---

PSL Manager
=======

~~~php
<?php

namespace League\Uri\PublicSuffix;

use Psr\SimpleCache\CacheInterface;

final class ICANNSectionManager
{
    const PSL_URL = 'https://publicsuffix.org/list/public_suffix_list.dat';
    public function __construct(CacheInterface $cache, HttpClient $http)
    public function getRules(string $source_url = self::PSL_URL): Rules
    public function refreshRules(string $source_url = self::PSL_URL): bool
}
~~~

This class obtains, parses, caches, and returns a PHP representation of the PSL ICANN section rules.

## Creating a new manager

To work as intended, the `ICANNSectionManager` constructor requires:

- a [PSR-16](http://www.php-fig.org/psr/psr-16/) Cache object to store the retrieved rules using a basic HTTP client.

- a `HttpClient` interface which exposes the `HttpClient::getContent` method which expects a string URL representation has its sole argument and returns the body from the given URL resource as a string.  
If an error occurs while retrieving such body a `HttpClientException` is thrown.

~~~php
<?php

namespace League\Uri\PublicSuffix;

interface HttpClient
{
    /**
     * Returns the content fetched from a given URL.
     *
     * @param string $url
     *
     * @throws HttpClientException If an errors occurs while fetching the content from a given URL
     *
     * @return string Retrieved content
     */
    public function getContent(string $url): string;
}
~~~

For advance usages you are free to use your own cache and/or http implementation.

By default and out of the box, the package uses:

- a file cache PSR-16 implementation based on the excellent [FileCache](https://github.com/kodus/file-cache) which **caches the local copy for a maximum of 7 days**.
- a HTTP client based on the cURL extension.

## Accessing the Public Suffix rules

~~~php
<?php

public function getRules(string $source_url = self::PSL_URL): Rules
~~~

This method returns a [Rules](/domain-parser/1.0/rules/) object which is instantiated with the PSL ICANN Section rules.

The method takes an optional `$source_url` argument which specifies the PSL ICANN Section source URL. If no local cache exists for the submitted source URL, the method will:

1. call `ICANNSectionManager::refreshRules` with the given URL to update its local cache
2. instantiate the `Rules` object with the newly cached data.

On error, the method throws an `League\Uri\PublicSuffix\Exception`.

~~~php
<?php

use League\Uri\PublicSuffix\Cache;
use League\Uri\PublicSuffix\CurlHttpClient;
use League\Uri\PublicSuffix\ICANNSectionManager;

$manager = new ICANNSectionManager(new Cache(), new CurlHttpClient());
$icann_rules = $manager->getRules('https://publicsuffix.org/list/public_suffix_list.dat');
$icann_rules->resolve('www.bébé.be');
~~~

## Refreshing the cached rules

This method enables refreshing your local copy of the PSL ICANN Section stored with your [PSR-16](http://www.php-fig.org/psr/psr-16/) Cache and retrieved using the Http Client. By default the method will use the `ICANNSectionManager::PSL_URL` as the source URL but you are free to substitute this URL with your own.  
The method returns a boolean value which is `true` on success.

~~~php
<?php

use League\Uri\PublicSuffix\Cache;
use League\Uri\PublicSuffix\CurlHttpClient;
use League\Uri\PublicSuffix\ICANNSectionManager;

$manager = new ICANNSectionManager(new Cache(), new CurlHttpClient());
$manager->refreshRules('https://publicsuffix.org/list/public_suffix_list.dat');
~~~

## Automatic Updates

It is important to always have an up to date PSL ICANN Section. In order to do so the library comes bundle with an auto-update script located in the `bin` directory.

~~~bash
$ php ./bin/update-psl-icann-section
~~~

This script requires that:

- The `League\Uri\Installer\ICANNSection` class which comes bundle with this package
- The use of the Cache and HTTP Client implementations bundle with the package.

If you prefer using your own implementations you should:

1. Copy the `League\Uri\Installer\ICANNSection` class
2. Adapt its code to reflect your requirements.


In any cases your are required to register a cron with your chosen script to keep your data up to date

For example, below I'm using the `ICANNSectionManager` with

- the [Symfony Cache component](https://github.com/symfony/cache)
- the [Guzzle](https://github.com/guzzle/guzzle) client.

Of course you can add more setups depending on your usage.

<p class="message-notice">Be sure to adapt the following code to your own framework/situation. The following code is given as an example without warranty of it working out of the box.</p>

~~~php
<?php

use GuzzleHttp\Client as GuzzleClient;
use League\Uri\PublicSuffix\HttpClient;
use League\Uri\PublicSuffix\HttpClientException;
use League\Uri\PublicSuffix\ICANNSectionManager;
use Symfony\Component\Cache\Simple\PDOCache;

final class GuzzleHttpClientAdapter implements HttpClient
{
    private $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function getContent(string $url): string
    {
        try {
            return $client->get($url)->getBody()->getContents();
        } catch (Throwable $e) {
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

$dbh = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'dbuser', 'dbpass');
$symfonyCache = new PDOCache($dbh, 'league-psl-icann', 86400);
$guzzleAdapter = new GuzzleHttpClientAdapter(new GuzzleClient());
$manager = new ICANNSectionManager($symfonyCache, $guzzleAdapter);
$manager->refreshRules();
//the rules are saved to the database for 1 day
//the rules are fetched using GuzzlClient

$icann_rules = $manager->getRules();
$domain = $icann_rules->resolve('nl.shop.bébé.faketld');
$domain->getDomain();            //returns 'nl.shop.bébé.faketld'
$domain->getPublicSuffix();      //returns 'faketld'
$domain->getRegistrableDomain(); //returns 'bébé.faketld'
$domain->getSubDomain();         //returns 'nl.shop'
$domain->isValid();              //returns false
~~~

In any case, you should setup a cron to regularly update your local cache.