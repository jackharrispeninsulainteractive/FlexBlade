<?php

namespace FlexBlade\Blade;

use Exception;
use Lucent\Facades\FileSystem;

/**
 * Directives
 *
 * Handles processing of Blade control directives like @php, @use, etc.
 * Each method processes a specific directive type and returns the PHP equivalent.
 */
class Directives
{

    public static array $used = [];

    /**
     * Process a @php directive
     *
     * Converts @php ... @endphp blocks to PHP code blocks
     *
     * @param array $detection The regex match for the directive
     * @return string PHP code block
     */
    public static function php(array $detection) : string
    {
        // Extract the PHP code from the directive and wrap it in PHP tags
        return "<?php ".$detection[1]."?>";
    }

    /**
     * Process a @use "directive"
     *
     * Converts @use('Namespace\Class') to PHP use statements
     *
     * @param array $detection The regex match for the directive
     * @return string PHP use statement
     */
    public static function use(array $detection) : string
    {
        // Convert to a PHP use statement & prevent multiple imports
        if(!in_array($detection[1], self::$used)){
            self::$used[] = $detection[1];
            return "<?php use ".$detection[1]."; ?>";
        }

        return "";
    }

    /**
     * Process a @foreach directive
     *
     * Converts @foreach($items as $key => $value) ... @endforeach blocks to PHP foreach loops
     * Supports both $key => $value and $value-only syntax, automatically providing an $index variable
     * in the latter case. Also processes any {{$key}} or {{$value}} expressions within the loop block.
     *
     * @param array $detection The regex match containing [1]=collection, [2]=key/item, [3]=value(optional), [4]=content
     * @return string PHP foreach loop
     */
    public static function foreach(array $detection) : string
    {
        $dataset = BladeCompiler::resolveViewBagCall($detection[1]);

        if(trim($detection[3]) === '') {
            $value = $detection[2];
            $detection[2] = '$index';
            $detection[3] = $value;
        }

        $output = "<?php foreach(".$dataset[0].' as '.$detection[2].' => '.$detection[3].'){?>';
        $output .= $detection[4];
        $output .= "<?php } ?>";

        // Capture the return value of preg_replace_callback
        return preg_replace_callback(BladeCompiler::$syntaxDefinitions["patterns"]["variable"], function($matches) {
            return str_replace($matches[0], "<?php echo $matches[1]; ?>", $matches[0]);
        }, $output);
    }

    /**
     * Process a @include directive
     *
     * Converts @include('Component/path') to a PHP include_once statement
     * Handles conversion of dot notation to file system path separators
     * Resolves the full path to Blade Components directory
     *
     * @param array $detection The regex match containing the include file path
     * @return string PHP include_once statement with resolved file path
     *
     * @throws Exception If the included file does not exist
     */
    public static function include(array $detection) : string
    {
        $detection[1] = str_replace(".",DIRECTORY_SEPARATOR,$detection[1]);
        $path = FileSystem::rootPath().DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR."Views".DIRECTORY_SEPARATOR.$detection[1].".blade.php";
        return "<?php include_once '$path'; ?>";
    }

    /**
     * Process a @if directive
     *
     * Converts @if(x operator y) ... @endif blocks to php
     *
     * @param array $detection The regex matches the code block and contents
     * @return string PHP if code block
     */
    public static function if(array $detection) : string
    {
        $output = explode(")", $detection[1]);

        $html = array_pop($output);

        $code = implode(")", $output).")";

        $matches = [];

        if (preg_match('/\$view->([a-zA-Z0-9_]+)/', $code, $matches)) {
            $result = BladeCompiler::resolveViewBagCall($matches[0]);
            $code = str_replace($result[1], $result[0], $code);
        }

        return "<?php if($code) {echo '$html';}; ?>";
    }

}