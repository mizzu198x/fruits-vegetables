<?php

declare(strict_types=1);

namespace App\Exception;

use App\Helper\ContextualTrait;
use App\Helper\ContextualInterface;

/**
 * @psalm-consistent-constructor
 */
class ContextualException extends \Exception implements ContextualInterface
{
    use ContextualTrait;

    protected const MESSAGE = 'Generic error';
    protected const CODE = 1000000;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $message = empty($message) ? static::MESSAGE : $message;
        $code = 0 === $code ? static::CODE : $code;

        parent::__construct($message, $code, $previous);
    }

    public static function forContext(array $context, ?\Throwable $previous = null): static
    {
        $exception = new static(static::MESSAGE, static::CODE, $previous);

        $exception->setContext($context);

        return $exception;
    }
}
