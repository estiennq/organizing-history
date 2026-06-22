<?php

namespace App\Entity;

use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $levelName = null;

    #[ORM\Column(length: 255)]
    private ?string $displayName = null;

    #[ORM\OneToMany(mappedBy: 'level', targetEntity: Difficulty::class, orphanRemoval: true)]
    private Collection $difficulties;

    #[ORM\ManyToMany(targetEntity: Room::class, mappedBy: 'levels')]
    private Collection $rooms;

    public function __construct()
    {
        $this->difficulties = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevelName(): ?string
    {
        return $this->levelName;
    }

    public function setLevelName(string $levelName): static
    {
        $this->levelName = $levelName;

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

    /**
     * @return Collection<int, Difficulty>
     */
    public function getDifficulties(): Collection
    {
        return $this->difficulties;
    }

    public function addDifficulty(Difficulty $difficulty): static
    {
        if (!$this->difficulties->contains($difficulty)) {
            $this->difficulties->add($difficulty);
            $difficulty->setLevel($this);
        }

        return $this;
    }

    public function removeDifficulty(Difficulty $difficulty): static
    {
        if ($this->difficulties->removeElement($difficulty)) {
            // set the owning side to null (unless already changed)
            if ($difficulty->getLevel() === $this) {
                $difficulty->setLevel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->addLevel($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            $room->removeLevel($this);
        }

        return $this;
    }
}
