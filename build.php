<?php

// build.php for LucentBlade package
if (ini_get('phar.readonly')) {
    ini_set('phar.readonly', 0);
}

// ANSI color codes for output
const COLORS = [
    'GREEN' => "\033[32m",
    'RED' => "\033[31m",
    'YELLOW' => "\033[33m",
    'BLUE' => "\033[34m",
    'RESET' => "\033[0m",
    'BOLD' => "\033[1m"
];

function log_step(string $message): void {
    echo COLORS['BLUE'] . "→ " . COLORS['RESET'] . $message . PHP_EOL;
}

function log_success(string $message): void {
    echo COLORS['GREEN'] . "✓ " . COLORS['RESET'] . $message . PHP_EOL;
}

function log_error(string $message): void {
    echo COLORS['RED'] . "✗ " . COLORS['RESET'] . $message . PHP_EOL;
}

function log_warning(string $message): void {
    echo COLORS['YELLOW'] . "! " . COLORS['RESET'] . $message . PHP_EOL;
}

function log_header(string $message): void {
    echo PHP_EOL . COLORS['BOLD'] . COLORS['BLUE'] . "=== " . $message . " ===" . COLORS['RESET'] . PHP_EOL;
}

// Start build process
log_header("Starting LucentBlade Package Build Process");

$pharFile = 'lucent-blade.phar';
$sourceDir = __DIR__ . DIRECTORY_SEPARATOR . "src";
$packageDir = $sourceDir . DIRECTORY_SEPARATOR . "LucentBlade";

// Verify source directory exists
if (!is_dir($packageDir)) {
    log_error("LucentBlade source directory not found: $packageDir");
    exit(1);
}

// Clean up existing PHAR
if (file_exists($pharFile)) {
    log_step("Removing existing PHAR file...");
    try {
        unlink($pharFile);
        log_success("Removed existing PHAR file");
    } catch (Exception $e) {
        log_error("Failed to remove existing PHAR: " . $e->getMessage());
        exit(1);
    }
}

// Create new PHAR
log_step("Creating new PHAR archive...");
try {
    $phar = new Phar($pharFile);
} catch (Exception $e) {
    log_error("Failed to create PHAR: " . $e->getMessage());
    exit(1);
}

// Count files to be added
$fileCount = iterator_count(
    new RegexIterator(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($packageDir)
        ),
        '/\.php$/'
    )
);

log_step("Found $fileCount PHP files to package");

// Start adding files
log_step("Adding LucentBlade files to PHAR...");
try {
    // Add all PHP files from the LucentBlade directory
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($packageDir, FilesystemIterator::SKIP_DOTS)
    );

    $addedFiles = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            // Get relative path from the src directory
            $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $phar->addFile($file->getPathname(), $relativePath);
            $addedFiles++;
            log_step("Added: " . $relativePath);
        }
    }

    log_success("Successfully added $addedFiles files to PHAR");
} catch (Exception $e) {
    log_error("Failed to build PHAR: " . $e->getMessage());
    exit(1);
}

// Create and set stub for LucentBlade package
log_step("Creating PHAR stub...");
$stub = <<<'EOF'
<?php
/**
 * LucentBlade Package PHAR
 * Blade templating engine for the Lucent Framework
 */

Phar::mapPhar();

// Autoloader for LucentBlade classes
spl_autoload_register(function ($class) {
    // Only handle LucentBlade namespace
    if (str_starts_with($class, 'LucentBlade\\')) {
        $file = 'phar://' . __FILE__ . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// If called directly, show package info
if (PHP_SAPI === 'cli' && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    $phar = new Phar(__FILE__);
    $metadata = $phar->getMetadata();
    
    echo "LucentBlade Package v" . ($metadata['version'] ?? 'unknown') . PHP_EOL;
    echo "Blade templating engine for the Lucent Framework" . PHP_EOL;
    echo PHP_EOL;
    echo "Classes available:" . PHP_EOL;
    
    $classes = [
        'LucentBlade\BladeResponse',
        'LucentBlade\ViewResponse', 
        'LucentBlade\Facades\Blade'
    ];
    
    foreach ($classes as $class) {
        $status = class_exists($class, false) ? '[LOADED]' : '[AVAILABLE]';
        echo "  $status $class" . PHP_EOL;
    }
    
    echo PHP_EOL;
    echo "Usage: Include this PHAR in your Lucent project." . PHP_EOL;
}

__HALT_COMPILER();
EOF;

try {
    $phar->setStub($stub);
    log_success("Successfully set PHAR stub");
} catch (Exception $e) {
    log_error("Failed to set PHAR stub: " . $e->getMessage());
    exit(1);
}

// Set metadata for the package
$version = 'v' . date('ymd');
$metadata = [
    'name' => 'LucentBlade',
    'version' => $version,
    'description' => 'Blade templating engine for the Lucent Framework',
    'built_at' => date('c'),
    'build_type' => 'package',
    'lucent_compatible' => '>=1.0.0',
    'classes' => [
        'LucentBlade\BladeResponse',
        'LucentBlade\ViewResponse',
        'LucentBlade\Facades\Blade'
    ]
];

try {
    $phar->setMetadata($metadata);
    log_success("Set package metadata");
} catch (Exception $e) {
    log_warning("Failed to set metadata: " . $e->getMessage());
}

// Verify PHAR
log_step("Verifying PHAR file...");
try {
    $verify = new Phar($pharFile);
    $fileCount = count($verify);
    $verifyMetadata = $verify->getMetadata();
    log_success("PHAR verification successful ($fileCount files)");
    log_success("Package: " . $verifyMetadata['name'] . " v" . $verifyMetadata['version']);
} catch (Exception $e) {
    log_error("PHAR verification failed: " . $e->getMessage());
    exit(1);
}

// Calculate file size and show completion
$fileSize = round(filesize($pharFile) / 1024, 2);
log_success("Build complete! LucentBlade package size: {$fileSize}KB");

// Show integration instructions
log_header("Integration Instructions");
echo COLORS['YELLOW'] . "To use this package:" . COLORS['RESET'] . PHP_EOL;
echo "1. Copy lucent-blade.phar to your project's packages/ directory" . PHP_EOL;
echo "2. Include it in your project: require_once 'packages/lucent-blade.phar';" . PHP_EOL;
echo "3. Use in controllers: return new LucentBlade\\BladeResponse('view', \$data);" . PHP_EOL;
echo PHP_EOL;