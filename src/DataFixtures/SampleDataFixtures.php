<?php

namespace App\DataFixtures;

use App\Config\UserType;
use App\Entity\Hall;
use App\Entity\Seat;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SampleDataFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createUser('admin@email.com', UserType::ADMIN);
        $manager->persist($admin);
        $customer1 = $this->createUser('customer1@email.com', UserType::CUSTOMER);
        $manager->persist($customer1);
        $customer2 = $this->createUser('customer2@email.com', UserType::CUSTOMER);
        $manager->persist($customer2);

        $hall = $this->createHall('hall1');
        $manager->persist($hall);

        for ($rowNumber = 1; $rowNumber <= 10; $rowNumber++) {
            for ($seatNumber = 1; $seatNumber <= 10; $seatNumber++) {
                $seat = $this->createSeat($rowNumber, $seatNumber, $hall);
                $manager->persist($seat);
            }
        }

        $manager->flush();
    }

    private function createUser(string $email, UserType $userType): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'Password123'));
        $user->setType($userType);

        return $user;
    }

    private function createSeat(int $rowNumber, int $seatNumber, Hall $hall): Seat
    {
        $seat = new Seat();
        $seat->setRowNo($rowNumber);
        $seat->setSeatNumber($seatNumber);
        $seat->setHall($hall);

        return $seat;
    }

    private function createHall(string $name): Hall
    {
        $hall = new Hall();
        $hall->setName($name);

        return $hall;
    }
}
