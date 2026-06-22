<?php

namespace App\Entity;

use App\Repository\TakesPlaceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TakesPlaceRepository::class)]
class TakesPlace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $positionId = null;

    #[ORM\ManyToOne(inversedBy: 'takesPlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Difficulty $difficulty = null;

    #[ORM\ManyToOne(inversedBy: 'takesPlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Action $action = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPositionId(): ?string
    {
        return $this->positionId;
    }

    public function setPositionId(string $positionId): static
    {
        $this->positionId = $positionId;

        return $this;
    }

    public function getDifficulty(): ?Difficulty
    {
        return $this->difficulty;
    }

    public function setDifficulty(?Difficulty $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): static
    {
        $this->action = $action;

        return $this;
    }
}
