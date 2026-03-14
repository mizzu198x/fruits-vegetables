<?php

declare(strict_types=1);

namespace App\Contract\Response;

class ErrorResponse
{
    public const string DEFAULT_MESSAGE = 'General error';

    public const int DEFAULT_CODE = 1000000;

    public string $message = self::DEFAULT_MESSAGE;

    public int $code = self::DEFAULT_CODE;

    public array $errorList = [];

    public function toArray(): array
    {
        $errorList = [];
        if (count($this->errorList) > 0) {
            $errorList = ['errors' => $this->errorList];
        }

        return [
            'error' => array_merge([
                'message' => $this->message,
                'code' => $this->code,
            ], $errorList),
        ];
    }
}
