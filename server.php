<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Resolver enlaces simbólicos para archivos estáticos
$publicPath = __DIR__.'/public'.$uri;
if ($uri !== '/' && file_exists($publicPath)) {
    return false;
}

// Si no existe directamente, intentar resolver el enlace simbólico
if ($uri !== '/' && strpos($uri, '/storage/') === 0) {
    $realPath = realpath(__DIR__.'/public/storage');
    if ($realPath) {
        $relativePath = substr($uri, strlen('/storage/'));
        $storageFile = $realPath . '/' . $relativePath;
        if (file_exists($storageFile)) {
            return false;
        }
    }
}

require_once __DIR__.'/public/index.php';
