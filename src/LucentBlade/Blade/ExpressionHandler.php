<?php

namespace LucentBlade\Blade;

/**
 * ExpressionHandler
 *
 * Processes special expressions in Blade templates, such as the null coalescing
 * operator and isset checks. This class converts these expressions to the
 * appropriate PHP code.
 */
class ExpressionHandler
{
    /**
     * Handle the null coalescing operator (??) in Blade templates
     *
     * Takes expressions like "$variable ?? 'default'" and returns the
     * appropriate value based on whether the variable exists.
     *
     * @param string $input The full expression
     * @param array $props Available variables
     * @return string The resulting value
     */
    public static function variableOr(string $input, array $props): string
    {
        // Split the expression by the ?? operator
        $items = explode(" ?? ", $input);

        $key = substr($items[0],1);

        // If the variable exists in props, return its value
        if(array_key_exists($key, $props)){
            return BladeCompiler::stripQuotes($props[$key]);
        }

        // Otherwise return the default value
        return BladeCompiler::stripQuotes($items[1]);
    }

    /**
     * Handle isset expressions in Blade templates
     *
     * Converts isset() expressions to PHP code
     *
     * @param string $input The full expression
     * @return string PHP code to check if a variable is set
     */
    public static function isset(string $input, array $props = []): string
    {
        //Check if we need to resolve a view bag call.
        if(str_contains($input, '->')){
            $results = BladeCompiler::resolveViewBagCall($input,'/isset\(([^)]+)\)/');
            $input = str_replace($results[1],$results[0],$input);

            return "<?php echo $input; ?>";
        }

        // Pattern to match the variable inside isset()
        if (preg_match('/isset\(\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\)/', $input, $matches)) {
            // Return the captured variable name without the $ prefix
            //return $matches[1];
            if(array_key_exists($matches[1], $props)){
               return "<?php echo ".str_replace("isset($".$matches[1].")","true",$input)."; ?>";
            }
        }



        // Output as PHP code
        return "<?php echo $input; ?>";
    }
}