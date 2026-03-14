<?php

declare(strict_types=1);

namespace App\Helper;

trait ContextualTrait
{
    protected array $context = [];

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function addContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }
}
