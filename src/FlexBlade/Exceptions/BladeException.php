<?php

namespace FlexBlade\Exceptions;

/**
 * Base exception class for all FlexBlade exceptions
 */
class BladeException extends \Exception
{
    public private(set) array $context = [];

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    public function addContext(string $key, $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }
}