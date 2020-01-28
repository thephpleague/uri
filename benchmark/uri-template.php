<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/../vendor/autoload.php';

$template = 'https://uri.thephpleague.com/{foo}{?query,limit}';
$uriTemplate = new League\Uri\UriTemplate($template);
$data = [
    'foo' => 'foo',
    'query' => ['foo', 'bar', 'baz'],
    'limit' => 10,
];
for ($i = 0; $i < 100000; $i++) {
    $uriTemplate->expand($data);
}
