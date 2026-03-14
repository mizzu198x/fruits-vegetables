<?php

declare(strict_types=1);

namespace App\Exception\Http;

class UnprocessableEntityException extends ApiException
{
    protected const string MESSAGE = 'Unprocessable entity';
    protected const int CODE = 1000005;
    protected const int HTTP_CODE = 422;
}
