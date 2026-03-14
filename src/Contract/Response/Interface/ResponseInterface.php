<?php

declare(strict_types=1);

namespace App\Contract\Response\Interface;

interface ResponseInterface
{
    public function getHttpCode(): int;
}
