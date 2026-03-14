<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Plant;
use App\Enum\PlantType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plant>
 *
 * @method null|Plant find($id, $lockMode = null, $lockVersion = null)
 * @method null|Plant findOneBy(array $criteria, array $orderBy = null)
 * @method Plant[]    findAll()
 * @method Plant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plant::class);
    }

    public function findByGoldenId(int $goldenId): ?Plant
    {
        return $this->findOneBy(['goldenId' => $goldenId]);
    }

    public function findByType(PlantType $type): array
    {
        return $this->findBy(['type' => $type]);
    }

    public function findByTypeAndSearchCriterial(
        PlantType $type,
        ?string $query = null,
        ?int $minQty = null,
        ?int $maxQty = null,
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.type = :type')
            ->setParameter('type', $type);

        if (!empty($query)) {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:name)')
                ->setParameter('name', '%'.$query.'%');
        }

        if (null !== $minQty) {
            $qb->andWhere('p.quantity >= :minQty')
                ->setParameter('minQty', $minQty);
        }

        if (null !== $maxQty) {
            $qb->andWhere('p.quantity <= :maxQty')
                ->setParameter('maxQty', $maxQty);
        }

        return $qb->getQuery()->getResult();
    }
}
