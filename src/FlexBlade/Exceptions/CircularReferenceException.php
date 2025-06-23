<?php

namespace FlexBlade\Exceptions;

/**
 * Thrown when circular component references are detected
 */
class CircularReferenceException extends BladeException
{
    public private(set) array $componentStack;

    public function __construct(array $componentStack)
    {
        $this->componentStack = $componentStack;

        $stackTrace = implode(' -> ', $componentStack);
        parent::__construct(
            "Circular component reference detected: {$stackTrace}",
            500,
            null,
            ['component_stack' => $componentStack]
        );
    }
}