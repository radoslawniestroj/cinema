<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Seat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function isSeatReserved(Seat $seat): bool
    {
        return (bool) $this->createQueryBuilder('r')
            ->andWhere('r.seat = :seat')
            ->setParameter('seat', $seat)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Reservation $reservation, bool $flush = false): void
    {
        $this->getEntityManager()->persist($reservation);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reservation $reservation, bool $flush = false): void
    {
        $this->getEntityManager()->remove($reservation);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
