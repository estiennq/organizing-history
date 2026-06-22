<?php

namespace App\Entity;

use App\Repository\CompletesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompletesRepository::class)]
class Plays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $errorCount = 0;

    #[ORM\Column]
    private ?bool $isCompleted = false;

    #[ORM\ManyToOne(inversedBy: 'playedBy')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Difficulty $difficulty = null;

    #[ORM\ManyToOne(inversedBy: 'levelsPlayed')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $student = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getErrorCount(): ?int
    {
        return $this->errorCount;
    }

    public function incrementErrorCount(): static
    {
        $this->errorCount++;

        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;

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

    public function getStudent(): ?user
    {
        return $this->student;
    }

    public function setStudent(?user $student): static
    {
        $this->student = $student;

        return $this;
    }
}
