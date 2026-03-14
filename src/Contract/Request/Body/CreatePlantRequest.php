<?php

declare(strict_types=1);

namespace App\Contract\Request\Body;

use App\Contract\Request\Body\Trait\PlantRequestTrait;
use App\Contract\Request\Interface\RequestBodyInterface;
use App\Contract\Request\Interface\ValidatableRequestInterface;
use App\Helper\ContextualInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreatePlantRequest implements
    RequestBodyInterface,
    ValidatableRequestInterface,
    ContextualInterface
{
    use PlantRequestTrait;

    #[Assert\Type(type: 'integer')]
    #[Assert\NotNull]
    public int $goldenId;

    public function getContext(): array
    {
        return [
            'goldenId' => $this->goldenId,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit' => $this->unit->value,
        ];
    }
}
