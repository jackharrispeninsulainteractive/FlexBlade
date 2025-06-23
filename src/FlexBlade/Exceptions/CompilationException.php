<?php

namespace FlexBlade\Exceptions;

/**
 * Thrown when compilation fails
 */
class CompilationException extends BladeException
{
    private string $templateName;
    private string $compilationStage;

    public function __construct(string $message, string $templateName, string $compilationStage = 'unknown')
    {
        $this->templateName = $templateName;
        $this->compilationStage = $compilationStage;

        parent::__construct(
            "Compilation failed for template '{$templateName}' during {$compilationStage}: {$message}",
            500,
            null,
            ['template' => $templateName, 'stage' => $compilationStage]
        );
    }

    public function getTemplateName(): string { return $this->templateName; }
    public function getCompilationStage(): string { return $this->compilationStage; }
}