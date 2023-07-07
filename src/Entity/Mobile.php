<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MobileRepository;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * 
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "get_mobile",
 *          parameters = { 
 *              "id" = "expr(object.getId())" 
 *          }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"mobiles"})
 *  )
 * 
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "update_mobile",
 *          parameters = { 
 *              "id" = "expr(object.getId())"
 *          }
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"mobiles", "mobile"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * 
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_mobile",
 *          parameters = {
 *              "id" = "expr(object.getId())"
 *          }
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          groups = {"mobiles", "mobile"},
 *          excludeIf = "expr(not is_granted('ROLE_ADMIN'))"
 *      )
 * )
 * 
 */
#[ORM\Entity(repositoryClass: MobileRepository::class)]
class Mobile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['mobiles', 'mobile'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['mobiles', 'mobile'])]
    private ?string $model = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['mobiles', 'mobile'])]
    private ?string $mark = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Groups(['mobiles'])]
    private ?string $shortDescription = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Groups(['mobile'])]
    private ?string $longDescription = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getMark(): ?string
    {
        return $this->mark;
    }

    public function setMark(?string $mark): static
    {
        $this->mark = $mark;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): static
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

}
