<?php

cleanupDirectory(__DIR__ . '/temp_install');

const TEMP_INSTALL = __DIR__ . '/temp_install';

if(!is_dir(TEMP_INSTALL)) {
    mkdir(TEMP_INSTALL);
}

require_once __DIR__ . '/build.php';
rename(__DIR__ . '/flexblade.phar',__DIR__."/temp_install/flexblade.phar");
require_once __DIR__ . '/temp_install/flexblade.phar';
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
