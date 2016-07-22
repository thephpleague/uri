<?php

if (!function_exists('League\Uri\uri_reference')) {
    require __DIR__.'/src/functions.php';
}

spl_autoload_register(function ($class) {

    $prefix = 'League\Uri\\';
    if (0 !== strpos($class, $prefix)) {
        return;
    }

    $file = __DIR__
        .'/src/'
        .str_replace('\\', '/', substr($class, strlen($prefix)))
        .'.php';
    if (is_readable($file)) {
        require $file;
    }
});
