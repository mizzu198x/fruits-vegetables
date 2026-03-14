<?php

declare(strict_types=1);

namespace App\Exception\Http;

class BadRequestException extends ApiException
{
    protected const string MESSAGE = 'Bad request';
    protected const int CODE = 1000002;
    protected const int HTTP_CODE = 400;
}
