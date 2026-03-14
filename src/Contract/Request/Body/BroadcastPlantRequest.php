<?php

declare(strict_types=1);

namespace App\Contract\Request\Body;

use App\Contract\Request\Body\Trait\PlantRequestTrait;
use App\Contract\Request\Interface\RequestBodyInterface;
use App\Contract\Request\Interface\ValidatableRequestInterface;
use App\Enum\PlantType;
use App\Helper\ContextualInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BroadcastPlantRequest implements
    RequestBodyInterface,
    ValidatableRequestInterface,
    ContextualInterface
{
    use PlantRequestTrait;

    #[Assert\Type(type: 'integer')]
    #[Assert\NotNull]
    public int $id;

    #[Assert\Choice(choices: [PlantType::FRUIT, PlantType::VEGETABLE])]
    #[Assert\NotNull]
    public PlantType $type;

    public function getContext(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'quantity' => $this->quantity,
            'unit' => $this->unit->value,
        ];
    }
}
