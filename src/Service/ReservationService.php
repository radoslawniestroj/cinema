<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Seat;
use App\Entity\User;
use App\Repository\ReservationRepository;

class ReservationService
{
    public function reserveSeat(Seat $seat, User $user, ReservationRepository $repo): Reservation
    {
        if ($repo->isSeatReserved($seat)) {
            throw new \RuntimeException("Seat already reserved");
        }

        $reservation = (new Reservation())
            ->setSeat($seat)
            ->setUser($user)
            ->setCreatedAt(new \DateTimeImmutable());

        $repo->save($reservation, true);

        return $reservation;
    }

    public function cancel(Reservation $reservation, ReservationRepository $repo): bool
    {
        $repo->remove($reservation, true);

        return true;
    }
}
