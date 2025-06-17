<?php

cleanupDirectory(__DIR__ . '/temp_install');

$temp_install = __DIR__ . '/temp_install';

if(!is_dir($temp_install)) {
    mkdir($temp_install);
}

require_once __DIR__ . '/build.php';
rename(__DIR__ . '/lucent-blade.phar',__DIR__."/temp_install/lucent-blade.phar");
require_once __DIR__ . '/temp_install/lucent-blade.phar';
require_once __DIR__ . '/vendor/autoload.php';

function cleanupDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? cleanupDirectory($path) : unlink($path);
    }
    rmdir($dir);
}
