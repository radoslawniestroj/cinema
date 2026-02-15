<?php

namespace App\Entity;

use App\Repository\SeatRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SeatRepository::class)]
class Seat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['hall:list', 'hall:admin:list'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['hall:list', 'hall:admin:list'])]
    private int $rowNo;

    #[ORM\Column]
    #[Groups(['hall:list', 'hall:admin:list'])]
    private int $seatNumber;

    #[ORM\ManyToOne(inversedBy: 'seats')]
    #[ORM\JoinColumn(nullable: false)]
    private Hall $hall;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'seat')]
    #[Groups(['hall:list', 'hall:admin:list'])]
    private Collection $reservations;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRowNo(): int
    {
        return $this->rowNo;
    }

    public function setRowNo(int $rowNo): self
    {
        $this->rowNo = $rowNo;
        return $this;
    }

    public function getSeatNumber(): int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(int $seatNumber): self
    {
        $this->seatNumber = $seatNumber;
        return $this;
    }

    public function getHall(): Hall
    {
        return $this->hall;
    }

    public function setHall(Hall $hall): self
    {
        $this->hall = $hall;
        return $this;
    }

    public function isFree(): bool
    {
        return $this->reservations->isEmpty();
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }
}
