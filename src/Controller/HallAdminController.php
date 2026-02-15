<?php

namespace App\Controller;

use App\Entity\Hall;
use App\Entity\Seat;
use App\Repository\HallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin', name: 'api_')]
class HallAdminController extends AbstractController
{
    #[Route('/hall', methods: ['GET'])]
    public function get(HallRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => ['hall:admin:list']]);
    }

    #[Route('/hall', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $hall = new Hall();
        $hall->setName($data['name']);

        $rowsNumber = $data['rowsNumber'];
        $seatsNumber = $data['seatsNumber'];

        for ($rowNumber = 1; $rowNumber <= $rowsNumber; $rowNumber++) {
            for ($seatNumber = 1; $seatNumber <= $seatsNumber; $seatNumber++) {
                $seat = new Seat();
                $seat->setRowNo($rowNumber);
                $seat->setSeatNumber($seatNumber);
                $seat->setHall($hall);
                $hall->addSeat($seat);
            }
        }

        $entityManager->persist($hall);
        $entityManager->flush();

        return $this->json(['id' => $hall->getId()]);
    }

    #[Route('/hall/{id}', methods: ['PUT'])]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        HallRepository $hallRepository
    ): JsonResponse {
        /** @var Hall $hall */
        $hall = $hallRepository->find($id);

        if (!$hall) {
            return $this->json(['error' => 'Hall not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $hall->setName($data['name']);
        }

        if (isset($data['rowsNumber'], $data['seatsNumber'])) {
            $rowsNumber = $data['rowsNumber'];
            $seatsNumber = $data['seatsNumber'];

            $existingSeats = [];
            foreach ($hall->getSeats() as $seat) {
                $existingSeats[$seat->getRowNo() . '-' . $seat->getSeatNumber()] = $seat;
            }

            $newSeatKeys = [];

            for ($rowNumber = 1; $rowNumber <= $rowsNumber; $rowNumber++) {
                for ($seatNumber = 1; $seatNumber <= $seatsNumber; $seatNumber++) {

                    $key = $rowNumber . '-' . $seatNumber;
                    $newSeatKeys[$key] = true;

                    if (isset($existingSeats[$key])) {
                        continue;
                    }

                    $seat = new Seat();
                    $seat->setRowNo($rowNumber);
                    $seat->setSeatNumber($seatNumber);
                    $seat->setHall($hall);

                    $entityManager->persist($seat);
                    $hall->addSeat($seat);
                }
            }

            foreach ($existingSeats as $key => $seat) {
                if (!isset($newSeatKeys[$key])) {
                    $hall->removeSeat($seat);
                    $entityManager->remove($seat);
                }
            }
        }

        $entityManager->flush();

        return $this->json($hall, 200, [], ['groups' => ['hall:admin:list']]);
    }

    #[Route('/hall/{id}', methods: ['DELETE'])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        HallRepository $hallRepository
    ): JsonResponse {
        $hall = $hallRepository->find($id);

        if (!$hall) {
            return $this->json(['error' => 'Hall not found'], 404);
        }

        $entityManager->remove($hall);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
