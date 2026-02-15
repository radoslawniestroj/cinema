<?php

namespace App\Entity;

use App\Config\UserType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['hall:admin:list'])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    #[Groups(['hall:admin:list'])]
    private string $email;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(enumType: UserType::class)]
    private ?UserType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getType(): ?UserType
    {
        return $this->type;
    }

    public function setType(UserType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function eraseCredentials(): void
    {}

    public function getRoles(): array
    {
        return array_unique([sprintf('ROLE_%s', $this->getType()->value)]);
    }
}
