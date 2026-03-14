<?php

declare(strict_types=1);

namespace App\Service\Collection;

use App\Contract\Request\Query\SearchPlantRequest;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Enum\UnitType;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Plant\InvalidPlantTypeException;
use App\Helper\UnitConverter;
use App\Repository\PlantRepository;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractPlantCollection implements CollectionInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly PlantRepository $plantRepository,
        protected readonly UnitConverter $unitConverter,
    ) {
    }

    public function add(Plant $plant): void
    {
        if ($plant->getType() !== $this->getType()) {
            throw new InvalidPlantTypeException(sprintf('Invalid plant type. Expected "%s".', $this->getType()->value));
        }

        $this->entityManager->persist($plant);
        $this->entityManager->flush();
    }

    public function remove(int $id): void
    {
        $plant = $this->plantRepository->find($id);

        if (!$plant instanceof Plant || $plant->getType() !== $this->getType()) {
            throw new ResourceNotFoundException(sprintf('%s not found.', \ucfirst($this->getType()->value)));
        }

        $this->entityManager->remove($plant);
        $this->entityManager->flush();
    }

    public function list(): array
    {
        return $this->plantRepository->findByType($this->getType());
    }

    public function search(SearchPlantRequest $searchCriteria): array
    {
        return $this->plantRepository->findByTypeAndSearchCriterial(
            $this->getType(),
            $searchCriteria->query,
            $this->getQty($searchCriteria->minQty, $searchCriteria->unit),
            $this->getQty($searchCriteria->maxQty, $searchCriteria->unit)
        );
    }

    abstract public function getType(): PlantType;

    private function getQty(?float $qty, UnitType $unit): ?int
    {
        if (null !== $qty) {
            return $this->unitConverter->toGrams($qty, $unit);
        }

        return null;
    }
}
