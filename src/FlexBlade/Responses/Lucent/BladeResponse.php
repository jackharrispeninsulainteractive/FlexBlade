<?php
/**
 * Copyright Jack Harris
 * Peninsula Interactive - policyManager
 * Last Updated - 18/11/2023
 */

namespace FlexBlade\Responses\Lucent;

use FlexBlade\Blade;
use FlexBlade\Blade\BladeCompiler;
use Lucent\Http\HttpResponse;

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
    }

    /**
     * Render the Blade template to string output
     *
     * @return string The rendered template output
     */
    public function render(): string
    {
        return Blade::render($this->path, $this->data);
    }

    public function set_response_header(): void
    {
        if (!headers_sent()) {
            header("Content-Type: text/html; charset=utf-8");
        }
    }
}