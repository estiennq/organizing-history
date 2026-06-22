<?php

namespace App\Entity;

use App\Repository\DifficultyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DifficultyRepository::class)]
class Difficulty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $difficultyName = null;

    #[ORM\Column(length: 255)]
    private ?string $displayName = null;

    #[ORM\ManyToOne(inversedBy: 'difficulties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    #[ORM\OneToMany(mappedBy: 'difficulty', targetEntity: TakesPlace::class, orphanRemoval: true)]
    private Collection $takesPlaces;

    #[ORM\OneToMany(mappedBy: 'difficulty', targetEntity: Plays::class)]
    private Collection $playedBy;

    public function __construct()
    {
        $this->takesPlaces = new ArrayCollection();
        $this->playedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDifficultyName(): ?string
    {
        return $this->difficultyName;
    }

    public function setDifficultyName(?string $difficultyName): static
    {
        $this->difficultyName = $difficultyName;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, TakesPlace>
     */
    public function getTakesPlaces(): Collection
    {
        return $this->takesPlaces;
    }

    public function addTakesPlace(TakesPlace $takesPlace): static
    {
        if (!$this->takesPlaces->contains($takesPlace)) {
            $this->takesPlaces->add($takesPlace);
            $takesPlace->setDifficulty($this);
        }

        return $this;
    }

    public function removeTakesPlace(TakesPlace $takesPlace): static
    {
        if ($this->takesPlaces->removeElement($takesPlace)) {
            // set the owning side to null (unless already changed)
            if ($takesPlace->getDifficulty() === $this) {
                $takesPlace->setDifficulty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Plays>
     */
    public function getPlayedBys(): Collection
    {
        return $this->playedBy;
    }

    public function addPlayedBy(Plays $playedBy): static
    {
        if (!$this->playedBy->contains($playedBy)) {
            $this->playedBy->add($playedBy);
            $playedBy->setDifficulty($this);
        }

        return $this;
    }

    public function removePlayedBy(Plays $playedBy): static
    {
        if ($this->playedBy->removeElement($playedBy)) {
            // set the owning side to null (unless already changed)
            if ($playedBy->getDifficulty() === $this) {
                $playedBy->setDifficulty(null);
            }
        }

        return $this;
    }
}
