<?php

namespace FlexBlade\Exceptions;

/**
 * Thrown when a component file cannot be found
 */
class ComponentNotFoundException extends BladeException
{
    public private(set) string $componentName;
    public private(set) string $expectedPath;

    public function __construct(string $componentName, string $expectedPath)
    {
        $this->componentName = $componentName;
        $this->expectedPath = $expectedPath;

        parent::__construct(
            "Component '{$componentName}' not found. Expected at: {$expectedPath}",
            404,
            null,
            ['component' => $componentName, 'expected_path' => $expectedPath]
        );
    }

    public function getComponentName(): string { return $this->componentName; }
    public function getExpectedPath(): string { return $this->expectedPath; }
}