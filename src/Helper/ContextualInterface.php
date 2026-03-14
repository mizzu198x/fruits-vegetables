<?php

declare(strict_types=1);

namespace App\Helper;

interface ContextualInterface
{
    public function getContext(): array;
}
