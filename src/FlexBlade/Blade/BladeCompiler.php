<?php

namespace FlexBlade\Blade;

use Exception;
use FlexBlade\Blade;
use FlexBlade\Minifier\Minify;
use FlexBlade\View;

/**
 * BladeCompiler
 *
 * Core class for compiling Blade templates into executable PHP code.
 * This class is responsible for parsing Blade templates, resolving layouts,
 * processing directives, and handling variable interpolation.
 */
class BladeCompiler
{
    /**
     * Definitions of supported Blade syntax patterns and their handlers
     *
     * Each pattern is mapped to a handler that processes matches during compilation
     *
     * @var array
     */
    public static final array $syntaxDefinitions = [
        "directives"=>[
            // Component with no closing tag: <x-alert type="error" />
            '/<x-([a-zA-Z0-9\-_.]+)\s*(.*?)\/>/s' =>[
                "handler" => [BladeComponents::class,"anonymous"],
                "name" => "directive.anonymous.self_closing",
            ],
            // Component with content: <x-alert>Content here</x-alert>
            '/<x-([a-zA-Z0-9\-_.]+)\s*(.*?)>(.*?)<\/x-\1>/s' => [
                "handler" => [BladeComponents::class,"anonymousWithBody"],
                "name" => "directive.anonymous_with_content",
            ],
            // PHP code blocks: @php echo 'Hello'; @endphp
            '/@php\s*(.+?)\s*@endphp/s' => [
                "handler" => [Directives::class,"php"],
                "name" => "directive.php"
            ],
            // Use statements: @use('App\Models\User')
            "/@use\('([^']*)'\s*((?:,\s*'[^']*')*)\)/" =>[
                "handler" => [Directives::class,"use"],
                "name" => "directive.use"
            ],
            "/@foreach\s*\(\s*(.*?)\s*as\s*(.*?)\s*(?:=>\s*(.*?))?\s*\)([\s\S]*?)@endforeach/" =>[
                "handler" => [Directives::class,"foreach"],
                "name" => "directive.foreach"
            ],
            '/@include\([\'"]([^\'"\)]+)[\'"]\)/' => [
                "handler" => [Directives::class,"include"],
                "name" => "directive.include"
            ],
            '/@if\s*(.+?)\s*@endif/s' =>[
                "handler" => [Directives::class,"if"],
                "name" => "directive.if"
            ]
        ],
        "expressions"=>[
            "isset"=>[
                "handler" => "",
                "name" => "expression.isset",
            ]
        ],
        "patterns"=>[
            "variable" => '/\{\{(.+?)}}/s',
            "extends" => '/@extends\(\'[^\']*\'\)/',
            "keyValuePair" => '~(\w+(?:-\w+)*)="([^"]*)"~',
            "quotes" => '/^([\'"])+|([\'"])+$/',
            "emptyQuotes" =>'/^[\'\"]+$/'
        ]
    ];

    private array $composerComponentNamespaces = [];

    /**
     * Render a Blade template to PHP output
     *
     * @param string $view Path to the view file
     * @param array $data Data to pass to the view
     * @return string Compiled PHP output
     * @throws Exception
     */
    public function render(string $view, array $data = []): string
    {

        if(!str_ends_with($view, ".blade.php")){
            $view.=".blade.php";
        }

        if(!file_exists(VIEWS.$view)){
            throw new Exception("Unable to load layout file from ".VIEWS.$view);
        }

        // Get the content of the view file
        $page = file_get_contents(VIEWS.$view);

        // Resolve the layout if the view extends a layout
        if(str_contains($page, "@extends(")) {

            $layout = $this->getBladeLayout($page);
            // Remove the @extends directive from the content
            $page = str_replace("@extends('".$layout["name"]."')","",$page);

                // Insert the page content into the layout's yield directive
            if(str_contains($layout["content"],"@yield('content')")){
                $page = str_replace("@yield('content')",$page,$layout["content"]);
            }
        }

        // Process all Blade syntax in the combined template
        $page = $this->processSyntax($page);

        $page = BladeCompiler::processVariables($data,$page);
        $minifier = new Minify();

        ob_start();
        extract($data);
        eval("?>".$page);

        return $minifier->minifyHtmlDocument(ob_get_clean());
    }

    /**
     * Process all Blade syntax in a template
     *
     * @param string $content Template content
     * @return string Processed content with PHP replacements
     */
    public function processSyntax(string $content): string
    {
        // Process each directive pattern
        foreach (BladeCompiler::$syntaxDefinitions["directives"] as $pattern => $properties) {
            $content = preg_replace_callback($pattern, function ($matches) use ($properties) {
                // Call the appropriate handler for each match
                return call_user_func($properties["handler"], $matches);
            }, $content);
        }

        return $content;
    }

    /**
     * Extract layout information from a Blade template
     *
     * @param string $input Blade template content
     * @return array|null Layout information including name, path, and content
     * @throws Exception
     */
    function getBladeLayout($input) : ?array
    {
        $component = [];

        // Find the @extends directive
        preg_match(BladeCompiler::$syntaxDefinitions["patterns"]["extends"], $input, $output_array);

        // Parse the layout name
        $name = str_replace("@extends('",'',$output_array[0]);
        $name = str_replace("')",'',$name);
        $component["name"] = $name;

        // Determine the layout file path
        $component["path"] = VIEWS.str_replace('.',DIRECTORY_SEPARATOR,$name).".blade.php";

        if(!file_exists($component["path"])){
            throw new Exception("Unable to load layout file from ".$component["path"]);
        }

        // Get the layout content
        $component["content"] = file_get_contents($component["path"]);

        // Replace variables in the layout
        foreach (Blade::Bag()->all() as $key => $value){
            if(is_string($value) or is_numeric($value)) {
                $component["content"] = str_replace('{{$' . $key . '}}', $value, $component["content"]);
            }
        }

        return $component;
    }

    /**
     * Convert a string of HTML attributes to a key-value array
     *
     * @param string $props HTML attribute string (e.g., 'class="btn" id="submit"')
     * @return array Associative array of attributes
     */
    public static function propertiesToKeyValuePair(string $props): array
    {
        // Use preg_match_all to extract all attribute name-value pairs
        preg_match_all(BladeCompiler::$syntaxDefinitions["patterns"]["keyValuePair"], $props, $matches, PREG_SET_ORDER);

        // Create an array to store key-value pairs
        $attributes = [];

        // Extract key-value pairs from the matches
        foreach ($matches as $match) {
            $key = $match[1];
            $value = $match[2];
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    /**
     * Remove quotes from a string
     *
     * @param string $string String that may have quotes
     * @return string String with quotes removed
     */
    public static function stripQuotes(string $string): string
    {
        // Strip all consecutive quotes (single or double) from beginning and end
        $result = preg_replace(BladeCompiler::$syntaxDefinitions["patterns"]["quotes"], '', $string);

        // Optional: Convert a string with only quotes to empty string
        if (preg_match(BladeCompiler::$syntaxDefinitions["patterns"]["emptyQuotes"], $result)) {
            return '';
        }

        return (string)$result;
    }

    /**
     * Process variable interpolation in a template
     *
     * @param array $props Variables to replace in the content
     * @param string $content Template content
     * @return string Content with variables replaced
     * @throws Exception
     */
    public static function processVariables(array $props, string $content, bool $component = false): string
    {
        if($component){
            return preg_replace_callback(BladeCompiler::$syntaxDefinitions["patterns"]["variable"], function($matches) use ($props) {
                $key = substr(trim($matches[1]),1);

                if(!array_key_exists($key, $props)){
                    $availableVars = empty($props) ? 'none' : "'" . implode("', '", array_keys($props)) . "'";
                    throw new Exception(
                        "Variable \"\${$key}\" is not available in this component. " .
                        "Available variables: {$availableVars}. " .
                        "Check your component usage and ensure you're passing the correct props."
                    );
                }

                return $props[$key];
            }, $content);
        }

        return preg_replace_callback(BladeCompiler::$syntaxDefinitions["patterns"]["variable"], function($matches) use ($props) {

            $key = trim($matches[1]);

            // Handle variable references
            if(str_starts_with($matches[1], "$")){
                $key = substr($matches[1],1);
            } else {
                // Handle function calls
                if(str_starts_with($key, 'isset')){
                    return ExpressionHandler::isset($matches[1],$props);
                }

                throw new Exception("Invalid function called");
            }

            // Handle the null coalescing operator (??)
            if(str_contains($key, ' ?? ')){
                return ExpressionHandler::variableOr($matches[1],$props);
            }

            // Handle object property access (->)
            if(str_contains($key, '->') && str_starts_with($key, 'view')){
                $result = "";
                $phpCode = "\FlexBlade\View\View::Bag()->all()";
                $sections = explode("->", $key);

                foreach ($sections as $index => $section) {
                    if($section === "view"){
                        $result = View::Bag()->all();
                    }else{

                        if(is_array($result)){
                            $phpCode .= "['".$section."']";
                            $result = $result[$section];
                            continue;
                        }

                        if(method_exists($result, $section)){
                            return "<?php echo ".$phpCode."->".$section."(); ?>";
                        }

                        if(property_exists($result, $section)){
                            return "<?php echo ".$phpCode."->".$section."; ?>";
                        }
                    }
                }

                $phpCode = "<?php echo " . $phpCode . "; ?>";

                //return $result;
                return $phpCode;
            }

            return "<?php echo $" . $key . "; ?>";
        }, $content);
    }

    public static function resolveViewBagCall(String $input,?String $pattern = null): array
    {

        if($pattern !== null) {
            // Perform the regex match
            if (preg_match($pattern, $input, $matches)) {
                // Return the content inside the parentheses
                // Handle object property access (->)
                if (str_contains($matches[1], '->')) {
                    return BladeCompiler::processViewBagCall($matches[1]);
                }
            }
        }

        return BladeCompiler::processViewBagCall($input);
    }

    public static function processViewBagCall(String $input) : array
    {
        if(str_contains($input, '->')){
            $result = [];
            $result[1] = $input;
            foreach (explode("->",$input) as $section) {

                if($section === '$view'){
                    $result[0] = 'App\Extensions\View\View::Bag()->all()';
                }else{
                    $result[0] .="['".$section."']";
                }
            }
            return $result;
        }

        return [];
    }

    public function registerNamespace(string $namespace, $directory) : void
    {
        $this->composerComponentNamespaces[$namespace] = $directory;
    }
}