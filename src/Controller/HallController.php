<?php

namespace App\Controller;

use App\Entity\Hall;
use App\Entity\Seat;
use App\Repository\HallRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class HallController extends AbstractController
{
    #[Route('/halls', methods: ['GET'])]
    public function list(HallRepository $repo): JsonResponse
    {
        $halls = $repo->findAll();
        $data = [];

        /** @var Hall $hall */
        foreach ($halls as $hall) {
            $freeSeats = [];

            /** @var Seat $seat */
            foreach ($hall->getSeats() as $seat) {
                if ($seat->isFree()) {
                    $freeSeats[] = [
                        'id'  => $seat->getId(),
                        'rowNumber'  => $seat->getRowNo(),
                        'seatNumber' => $seat->getSeatNumber(),
                    ];
                }
            }

            $data[] = [
                'id'    => $hall->getId(),
                'name'  => $hall->getName(),
                'seats' => $freeSeats,
            ];
        }

        return new JsonResponse($data);
    }
}
