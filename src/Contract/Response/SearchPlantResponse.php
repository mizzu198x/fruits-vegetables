<?php

declare(strict_types=1);

namespace App\Contract\Response;

use JMS\Serializer\Annotation as JMS;

class SearchPlantResponse extends AbstractResponse
{
    #[JMS\Type(name: 'array<App\Contract\Response\Model\Plant>')]
    public array $items = [];
}
