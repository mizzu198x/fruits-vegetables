<?php

declare(strict_types=1);

namespace App\Exception\Http;

use App\Exception\ContextualException;

class ApiException extends ContextualException
{
    protected const string MESSAGE = 'API error';
    protected const int CODE = 1000001;
    protected const int HTTP_CODE = 500;

    public function getStatusCode(): int
    {
        return static::HTTP_CODE;
    }
}
