<?php

namespace FlexBlade\Blade;

use Exception;
use FlexBlade\Blade;

/**
 * BladeComponents
 *
 * Handles the processing of Blade component directives in templates.
 * This class is responsible for resolving and rendering component templates,
 * including both self-closing components and those with content.
 */
class BladeComponents
{
    /**
     * Cache for component templates to avoid repeated file reads
     *
     * @var array
     */
    private static array $cache = [];

    /**
     * Process an anonymous component (either self-closing or with body)
     *
     * @param array $detection The regex match for the component tag
     * @param string $body Optional body content for the component
     * @return string Processed component output
     * @throws Exception
     */
    public static function anonymous(array $detection, string $body = "") : string
    {
        // Get the component name (first capture group from regex)
        $componentName = $detection[1];

        //Set our namespace
        $namespace = null;
        $directive = null;

        //Check if we are using a namespace
        if(str_contains($detection[0], '::')) {
            $componentName = substr($detection[2], 2);
            $namespace = $detection[1];

            $directory = Blade::compiler()->resolveNamespace($detection[1]);

            if($directory === null){
                throw new Exception(sprintf(
                    "Blade namespace '%s' not found when loading component:\n- %s",
                    $namespace,
                    $detection[0]
                ));
            }

            $directory.= DIRECTORY_SEPARATOR;
        }

        if($directory === null) {
            $directory = VIEWS;
        }

        // Load and cache the component template if not already cached
        if(!isset(self::$cache[$componentName])){
            $file = $directory.str_replace(".",DIRECTORY_SEPARATOR,$componentName).".blade.php";

            if(!file_exists($file)) {
                throw new Exception(sprintf(
                    "Blade component '%s' not found. Searched in:\n- %s",
                    $componentName,
                    $file
                ));
            }

            self::$cache[$componentName] = file_get_contents($file);
        }

        $output = self::$cache[$componentName];

        // Parse component attributes
        $props = BladeCompiler::propertiesToKeyValuePair($detection[2]);

        // Add the body content as a special $body variable
        $props['children'] = BladeCompiler::processVariables($props, $body);

        // Process variables in the component output
        return BladeCompiler::processVariables($props, $output,true);
    }

    /**
     * Process a component that has a body/content between opening and closing tags
     *
     * @param array $detection The regex match for the component tag with content
     * @return string Processed component output
     * @throws Exception
     */
    public static function anonymousWithBody(array $detection) : string
    {
        $processedContent = new BladeCompiler()->processSyntax($detection[3]);

        return self::anonymous($detection, $processedContent);
    }
}