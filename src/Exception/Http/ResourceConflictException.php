<?php

declare(strict_types=1);

namespace App\Exception\Http;

class ResourceConflictException extends ApiException
{
    protected const string MESSAGE = 'Resource already exists';
    protected const int CODE = 1000004;
    protected const int HTTP_CODE = 409;
}
