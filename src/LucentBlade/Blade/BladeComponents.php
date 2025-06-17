<?php

namespace LucentBlade\Blade;

use Exception;

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

        // Load and cache the component template if not already cached
        if(!isset(self::$cache[$componentName])){
            $file = VIEWS."Blade".DIRECTORY_SEPARATOR."Components".DIRECTORY_SEPARATOR.str_replace(".",DIRECTORY_SEPARATOR,$componentName).".blade.php";
            self::$cache[$componentName] = file_get_contents($file);
        }

        $output = self::$cache[$componentName];

        // Parse component attributes
        $props = BladeCompiler::propertiesToKeyValuePair($detection[2]);

        // Add the body content as a special $body variable
        $props['children'] = BladeCompiler::processVariables($props, $body);

        // Process variables in the component output
        return BladeCompiler::processVariables($props, $output);
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