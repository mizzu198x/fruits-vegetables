<?php

declare(strict_types=1);

namespace App\Exception\Http;

class ResourceNotFoundException extends ApiException
{
    protected const string MESSAGE = 'Resource Not Found';
    protected const int CODE = 1000003;
    protected const int HTTP_CODE = 404;
}
