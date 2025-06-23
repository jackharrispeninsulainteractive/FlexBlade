<?php

namespace FlexBlade\Blade;

use FlexBlade\Blade;
use FlexBlade\Exceptions\ComponentNotFoundException;
use FlexBlade\Exceptions\CircularReferenceException;
use FlexBlade\Exceptions\MaxDepthExceededException;
use FlexBlade\Exceptions\CompilationException;

/**
 * BladeComponents with robust error handling
 */
class BladeComponents
{
    private static array $cache = [];
    private static array $processingStack = [];
    private static int $maxDepth = 10;

    public static function anonymous(array $detection, string $body = "") : string
    {
        $componentName = $detection[1];

        try {
            // Check for circular references
            if (in_array($componentName, self::$processingStack)) {
                throw new CircularReferenceException([...self::$processingStack, $componentName]);
            }

            // Check maximum depth
            if (count(self::$processingStack) >= self::$maxDepth) {
                throw new MaxDepthExceededException(
                    count(self::$processingStack) + 1,
                    self::$maxDepth,
                    [...self::$processingStack, $componentName]
                );
            }

            // Add to processing stack
            self::$processingStack[] = $componentName;

            // Handle namespace resolution
            $namespace = null;
            $directory = VIEWS;

            if (str_contains($detection[0], '::')) {
                // For namespaced components like <x-ignite360::button.button>
                // $detection[1] contains "ignite360::button.button"
                $fullComponentPath = $detection[1];

                if (str_contains($fullComponentPath, '::')) {
                    $namespaceParts = explode('::', $fullComponentPath, 2);

                    if (count($namespaceParts) === 2) {
                        $namespace = $namespaceParts[0]; // "ignite360"
                        $componentName = $namespaceParts[1]; // "button.button"
                    } else {
                        throw new ComponentNotFoundException(
                            $fullComponentPath,
                            "Invalid namespace format: {$fullComponentPath}"
                        );
                    }

                    $directory = Blade::compiler()->resolveNamespace($namespace);
                }

                if ($directory === null) {
                    throw new ComponentNotFoundException(
                        $namespace . '::' . $componentName,
                        "Namespace '{$namespace}' not registered"
                    );
                }

                // Update the component name in the processing stack to include namespace
                array_pop(self::$processingStack);
                self::$processingStack[] = $namespace . '::' . $componentName;
            }

            // Load and cache the component template if not already cached
            $cacheKey = ($namespace ? $namespace . '::' : '') . $componentName;

            if (!isset(self::$cache[$cacheKey])) {
                $file = $directory.str_replace(".", DIRECTORY_SEPARATOR, $componentName) . ".blade.php";

                if (!file_exists($file)) {
                    throw new ComponentNotFoundException($cacheKey, $file);
                }

                $content = file_get_contents($file);
                if ($content === false) {
                    throw new CompilationException(
                        "Failed to read component file",
                        $cacheKey,
                        'file_reading'
                    );
                }

                self::$cache[$cacheKey] = $content;
            }

            $output = self::$cache[$cacheKey];

            // Parse component attributes
            $props = BladeCompiler::propertiesToKeyValuePair($detection[2]);

            // Add the body content as a special $children variable
            $props['children'] = BladeCompiler::processVariables($props, $body, true);

            // Process variables in the component output
            $result = BladeCompiler::processVariables($props, $output, true);

            // RECURSIVE PROCESSING: Process any nested components in the result
            $compiler = new BladeCompiler();
            $result = $compiler->processSyntax($result);

            // Remove from processing stack
            array_pop(self::$processingStack);

            return $result;

        } catch (\Exception $e) {
            // Clean up processing stack on any error
            $currentComponentName = ($namespace ? $namespace . '::' : '') . $componentName;
            if (($key = array_search($currentComponentName, self::$processingStack)) !== false) {
                array_splice(self::$processingStack, $key);
            }

            // Re-throw the exception
            throw $e;
        }
    }

    public static function anonymousWithBody(array $detection) : string
    {
        try {
            $processedContent = new BladeCompiler()->processSyntax($detection[3]);
            return self::anonymous($detection, $processedContent);
        } catch (\Exception $e) {
            // Add context about which component was being processed
            if (method_exists($e, 'addContext')) {
                $e->addContext('processing_component_with_body', $detection[1]);
            }
            throw $e;
        }
    }

    public static function setMaxDepth(int $depth): void
    {
        self::$maxDepth = $depth;
    }

    public static function getProcessingStack(): array
    {
        return self::$processingStack;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}