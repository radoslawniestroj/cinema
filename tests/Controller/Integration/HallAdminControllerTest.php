<?php

namespace App\Tests\Controller\Integration;

use App\Config\UserType;
use App\Entity\Hall;
use App\Entity\Seat;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HallAdminControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        $schemaTool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        $user = new User();
        $user->setEmail('admin@email.com');
        $user->setPassword('Password123');
        $user->setType(UserType::ADMIN);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);
    }

    public function testGetEmptyList(): void
    {
        $this->client->request('GET', '/api/admin/hall');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame([], $data);
    }

    public function testCreateHall(): void
    {
        $payload = [
            'name' => 'Main Hall',
            'rowsNumber' => 2,
            'seatsNumber' => 3
        ];

        $this->client->request('POST', '/api/admin/hall', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);

        $hall = $this->entityManager->getRepository(Hall::class)->find($data['id']);

        $this->assertNotNull($hall);
        $this->assertCount(6, $hall->getSeats());
    }

    public function testGetListAfterCreation(): void
    {
        $hall = new Hall();
        $hall->setName('Test Hall');

        for ($r = 1; $r <= 2; $r++) {
            for ($s = 1; $s <= 2; $s++) {
                $seat = new Seat();
                $seat->setHall($hall);
                $seat->setRowNo($r);
                $seat->setSeatNumber($s);
                $hall->addSeat($seat);
                $this->entityManager->persist($seat);
            }
        }

        $this->entityManager->persist($hall);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/admin/hall');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $data);
        $this->assertEquals('Test Hall', $data[0]['name']);
    }

    public function testEditHallChangeNameAndSeats(): void
    {
        $hall = new Hall();
        $hall->setName('Old Hall');
        for ($i = 1; $i <= 2; $i++) {
            $seat = new Seat();
            $seat->setHall($hall);
            $seat->setRowNo(1);
            $seat->setSeatNumber($i);
            $hall->addSeat($seat);
            $this->entityManager->persist($seat);
        }

        $this->entityManager->persist($hall);
        $this->entityManager->flush();
        $id = $hall->getId();

        $payload = [
            'name' => 'Updated Hall',
            'rowsNumber' => 2,
            'seatsNumber' => 2
        ];

        $this->client->request('PUT', "/api/admin/hall/$id", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseIsSuccessful();

        $updated = $this->entityManager->getRepository(Hall::class)->find($id);

        $this->assertEquals('Updated Hall', $updated->getName());
        $this->assertCount(4, $updated->getSeats());
    }

    public function testEditHallNotFound(): void
    {
        $this->client->request('PUT', '/api/admin/hall/999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['name' => 'Nope']));

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Hall not found', $data['error']);
    }

    public function testDeleteHall(): void
    {
        $hall = new Hall();
        $hall->setName('Delete Hall');

        $this->entityManager->persist($hall);
        $this->entityManager->flush();
        $id = $hall->getId();

        $this->client->request('DELETE', "/api/admin/hall/$id");

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);

        $deleted = $this->entityManager->getRepository(Hall::class)->find($id);
        $this->assertNull($deleted);
    }

    public function testDeleteHallNotFound(): void
    {
        $this->client->request('DELETE', '/api/admin/hall/999');

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Hall not found', $data['error']);
    }
}
