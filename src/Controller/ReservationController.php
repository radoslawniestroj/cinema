<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seat;
use App\Entity\User;
use App\Repository\SeatRepository;
use App\Repository\ReservationRepository;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ReservationController extends AbstractController
{
    #[Route('/reservation-create', methods: ['POST'])]
    public function reserve(
        Request $request,
        SeatRepository $seatRepo,
        ReservationService $service,
        ReservationRepository $reservationRepo,
        Security $security
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        /** @var Seat $seat */
        $seat = $seatRepo->find($data['seatId'] ?? null);
        /** @var User $user */
        $user = $security->getUser();

        if (!$seat) {
            return $this->json(['error' => 'Seat not found'], 404);
        }

        try {
            $reservation = $service->reserveSeat($seat, $user, $reservationRepo);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['id' => $reservation->getId()]);
    }

    #[Route('/reservation-cancel', methods: ['DELETE'])]
    public function cancel(
        Request $request,
        ReservationService $service,
        ReservationRepository $reservationRepo,
        Security $security
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        /** @var Reservation $reservation */
        $reservation = $reservationRepo->find($data['reservationId'] ?? null);
        /** @var User $user */
        $user = $security->getUser();

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], 404);
        }

        if ($reservation->getUser() !== $user) {
            return $this->json(['error' => 'Reservation does not belongs to you'], 404);
        }

        try {
            $service->cancel($reservation, $reservationRepo);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['message' => 'Cancelled']);
    }
}
