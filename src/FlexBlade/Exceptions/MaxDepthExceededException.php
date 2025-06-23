<?php

namespace FlexBlade\Exceptions;

/**
 * Thrown when component nesting exceeds maximum depth
 */
class MaxDepthExceededException extends BladeException
{
    public private(set) int $currentDepth;
    public private(set) int $maxDepth;
    public private(set) array $componentStack;

    public function __construct(int $currentDepth, int $maxDepth, array $componentStack)
    {
        $this->currentDepth = $currentDepth;
        $this->maxDepth = $maxDepth;
        $this->componentStack = $componentStack;

        $stackTrace = implode(' -> ', $componentStack);
        parent::__construct(
            "Component nesting depth ({$currentDepth}) exceeds maximum ({$maxDepth}). Stack: {$stackTrace}",
            500,
            null,
            [
                'current_depth' => $currentDepth,
                'max_depth' => $maxDepth,
                'component_stack' => $componentStack
            ]
        );
    }

    public function getMaxDepth(): int { return $this->maxDepth; }
    public function getComponentStack(): array { return $this->componentStack; }
}