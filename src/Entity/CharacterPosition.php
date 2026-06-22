<?php

namespace App\Entity;

use App\Repository\CharacterPositionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: CharacterPositionRepository::class)]
class CharacterPosition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $positionX = null;

    #[ORM\Column]
    private ?float $positionY = null;

    #[ORM\Column]
    private ?float $offsetX = null;

    #[ORM\Column]
    private ?float $offsetY = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'characterPositions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Scene $scene = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPositionX(): ?float
    {
        return $this->positionX;
    }

    public function setPositionX(float $positionX): static
    {
        $this->positionX = $positionX;

        return $this;
    }

    public function getPositionY(): ?float
    {
        return $this->positionY;
    }

    public function setPositionY(float $positionY): static
    {
        $this->positionY = $positionY;

        return $this;
    }

    public function getOffsetX(): ?float
    {
        return $this->offsetX;
    }

    public function setOffsetX(float $offsetX): static
    {
        $this->offsetX = $offsetX;

        return $this;
    }

    public function getOffsetY(): ?float
    {
        return $this->offsetY;
    }

    public function setOffsetY(float $offsetY): static
    {
        $this->offsetY = $offsetY;

        return $this;
    }

    public function getScene(): ?Scene
    {
        return $this->scene;
    }

    public function setScene(?Scene $scene): static
    {
        $this->scene = $scene;

        return $this;
    }
}
