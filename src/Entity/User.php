<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 *  @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "get_user",
 *          parameters = { 
 *              "user_id" = "expr(object.getId())",
 *              "client_id" = "expr(object.getClient().getId())"
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"users"})
 * )
 * 
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "update_user",
 *          parameters = { 
 *              "user_id" = "expr(object.getId())", 
 *              "client_id" = "expr(object.getClient().getId())" 
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"users", "user"}
 *      )
 * )
 * 
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_user",
 *          parameters = { 
 *              "user_id" = "expr(object.getId())", 
 *              "client_id" = "expr(object.getClient().getId())" 
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"users", "user"})
 * )
 */

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user', 'users'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy:'users')]

    private ?Client $client = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user','users', 'create'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user','users', 'create'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user', 'create'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user', 'create'])]
    private ?string $adress = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user', 'create'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user', 'create'])]
    private ?string $city = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
