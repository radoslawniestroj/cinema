<?php

namespace App\Tests\Controller\Unit;

use App\Controller\HallAdminController;
use App\Entity\Hall;
use App\Entity\Seat;
use App\Repository\HallRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class HallAdminControllerTest extends TestCase
{
    public function testGetReturnsAllHalls()
    {
        $hall = new Hall();
        $hall->setName("Test Hall");

        $seat1 = new Seat();
        $seat1->setRowNo(1);
        $seat1->setSeatNumber(1);
        $seat1->setHall($hall);
        $hall->addSeat($seat1);

        $seat2 = new Seat();
        $seat2->setRowNo(1);
        $seat2->setSeatNumber(2);
        $seat2->setHall($hall);
        $hall->addSeat($seat2);

        $repo = $this->createStub(HallRepository::class);
        $repo->method('findAll')->willReturn([$hall]);

        $controller = new HallAdminController();
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->get($repo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertCount(2, $hall->getSeats());
    }

    public function testCreateHall()
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $controller = new HallAdminController;
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);

        $requestData = [
            'name' => 'New Hall',
            'rowsNumber' => 2,
            'seatsNumber' => 2,
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $response = $controller->create($request, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
    }

    public function testEditName(): void
    {
        $hall = new Hall();
        $hall->setName("Old Name");

        $hallRepository = $this->createStub(HallRepository::class);
        $hallRepository->method('find')->with(1)->willReturn($hall);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $controller = new HallAdminController();
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);

        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'New Name'
        ]));

        $response = $controller->edit(
            1,
            $request,
            $entityManager,
            $hallRepository
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals('New Name', $hall->getName());
    }

    public function testEditSeatsAddAndRemove(): void
    {
        $hall = new Hall();
        $hall->setName("Test Hall");

        $seat1 = new Seat();
        $seat1->setRowNo(1);
        $seat1->setSeatNumber(1);
        $seat1->setHall($hall);
        $hall->addSeat($seat1);

        $seat2 = new Seat();
        $seat2->setRowNo(1);
        $seat2->setSeatNumber(2);
        $seat2->setHall($hall);
        $hall->addSeat($seat2);

        $hallRepository = $this->createStub(HallRepository::class);
        $hallRepository->method('find')->with(1)->willReturn($hall);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(Seat::class));
        $entityManager->expects($this->never())->method('remove');
        $entityManager->expects($this->once())->method('flush');

        $controller = new HallAdminController();
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);

        $request = new Request([], [], [], [], [], [], json_encode([
            'rowsNumber' => 2,
            'seatsNumber' => 2,
        ]));

        $response = $controller->edit(1, $request, $entityManager, $hallRepository);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(4, $hall->getSeats());
    }

    public function testEditRemovesOldSeats(): void
    {
        $hall = new Hall();
        $hall->setName("Test Hall");

        $seat1 = new Seat();
        $seat1->setRowNo(1);
        $seat1->setSeatNumber(1);
        $seat1->setHall($hall);
        $hall->addSeat($seat1);

        $seat2 = new Seat();
        $seat2->setRowNo(1);
        $seat2->setSeatNumber(2);
        $seat2->setHall($hall);
        $hall->addSeat($seat2);

        $hallRepository = $this->createStub(HallRepository::class);
        $hallRepository->method('find')->with(1)->willReturn($hall);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('persist');
        $entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($seat2);
        $entityManager->expects($this->once())->method('flush');

        $controller = new HallAdminController();
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);

        $request = new Request([], [], [], [], [], [], json_encode([
            'rowsNumber' => 1,
            'seatsNumber' => 1,
        ]));

        $response = $controller->edit(
            1,
            $request,
            $entityManager,
            $hallRepository
        );

        $this->assertSame(200, $response->getStatusCode());

        // Tylko jedno miejsce powinno pozostać
        $this->assertCount(1, $hall->getSeats());
    }

    public function testEditHallNotFound()
    {
        $repo = $this->createStub(HallRepository::class);
        $repo->method('find')->willReturn(null);

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $controller = new HallAdminController();
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->edit(1, new Request(), $entityManager, $repo);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Hall not found', $response->getContent());
    }

    public function testDelete()
    {
        $hall = new Hall();
        $hallRepository = $this->createStub(HallRepository::class);
        $hallRepository->method('find')->with(5)->willReturn($hall);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('remove')->with($hall);
        $entityManager->method('flush');

        $controller = new HallAdminController();
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);

        $response = $controller->delete(5, $entityManager, $hallRepository);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(json_encode(['success' => true]), $response->getContent());
    }

    public function testDeleteHallNotFound()
    {
        $repo = $this->createStub(HallRepository::class);
        $repo->method('find')->willReturn(null);

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $controller = new HallAdminController();
        $container = $this->createStub(ContainerInterface::class);
        $controller->setContainer($container);
        $response = $controller->delete(1, $entityManager, $repo);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Hall not found', $response->getContent());
    }
}
