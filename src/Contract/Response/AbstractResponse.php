<?php

declare(strict_types=1);

namespace App\Contract\Response;

use App\Contract\Response\Interface\ResponseInterface;

abstract class AbstractResponse implements ResponseInterface
{
    public const int HTTP_CODE_SUCCESS = 200;

    public function getHttpCode(): int
    {
        return static::HTTP_CODE_SUCCESS;
    }
}
