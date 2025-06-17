<?php
/**
 * Copyright Jack Harris
 * Peninsula Interactive - policyManager
 * Last Updated - 18/11/2023
 */

namespace LucentBlade;

use Lucent\Http\HttpResponse;
use LucentBlade\Blade\BladeCompiler;

/**
 * BladeResponse
 *
 * HTTP response wrapper for Blade templates. This class extends the
 * base HttpResponse class to provide functionality specific to
 * rendering Blade templates as HTTP responses.
 */
class BladeResponse extends HttpResponse
{
    /**
     * Path to the Blade template file
     *
     * @var string
     */
    private string $path;

    /**
     * Array of php variables
     *
     * @var array
     */
    private array $data;

    /**
     * The BladeCompiler instance used to compile templates
     *
     * @var BladeCompiler
     */
    private BladeCompiler $compiler;

    /**
     * Create a new BladeResponse
     *
     * @param string $path Path to the Blade template file
     * @param array $data Optional array of PHP variables
     */
    public function __construct(string $path, array $data = [])
    {
        // Initialize parent with empty content and 200 status code
        parent::__construct("", 200);

        $this->path = $path;
        $this->data = $data;
        $this->compiler = new BladeCompiler();
    }

    /**
     * Render the Blade template to string output
     *
     * @return string The rendered template output
     */
    public function render(): string
    {
        return $this->compiler->render($this->path,$this->data);
    }

    /**
     * Set the HTTP response headers for a Blade template
     *
     * @return void
     */
    public function set_response_header(): void
    {
        //header("Content-Type: text/html; charset=utf-8");
    }
}