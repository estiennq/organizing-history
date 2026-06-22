<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $roomName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roomDescription = null;

    #[ORM\ManyToMany(targetEntity: Level::class, inversedBy: 'rooms')]
    private Collection $levels;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $teacher = null;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: User::class, orphanRemoval: true)]
    private Collection $students;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
        $this->students = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomName(): ?string
    {
        return $this->roomName;
    }

    public function setRoomName(string $roomName): static
    {
        $this->roomName = $roomName;

        return $this;
    }

    public function getRoomDescription(): ?string
    {
        return $this->roomDescription;
    }

    public function setRoomDescription(?string $roomDescription): static
    {
        $this->roomDescription = $roomDescription;

        return $this;
    }

    /**
     * @return Collection<int, Level>
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): static
    {
        if (!$this->levels->contains($level)) {
            $this->levels->add($level);
        }

        return $this;
    }

    public function removeLevel(Level $level): static
    {
        $this->levels->removeElement($level);

        return $this;
    }

    public function getTeacher(): ?User
    {
        return $this->teacher;
    }

    public function setTeacher(?User $teacher): static
    {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(User $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->setRoom($this);
        }

        return $this;
    }

    public function removeStudent(User $student): static
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getRoom() === $this) {
                $student->setRoom(null);
            }
        }

        return $this;
    }
}
