<?php

namespace App\Entity;

use App\Repository\SceneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: SceneRepository::class)]
class Scene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $sprite = null;

    #[ORM\OneToMany(mappedBy: 'scene', targetEntity: CharacterPosition::class, orphanRemoval: true)]
    private Collection $characterPositions;

    #[ORM\OneToMany(mappedBy: 'scene', targetEntity: Action::class, orphanRemoval: true)]
    private Collection $actions;

    public function __construct()
    {
        $this->characterPositions = new ArrayCollection();
        $this->actions = new ArrayCollection();
    }

    public function __serialize(): array
    {
        // TODO: Implement __serialize() method.
        return ['id' => $this->id, 'Test' => 'test'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSprite(): ?string
    {
        return $this->sprite;
    }

    public function setSprite(string $sprite): static
    {
        $this->sprite = $sprite;

        return $this;
    }

    /**
     * @return Collection<int, CharacterPosition>
     */
    public function getCharacterPositions(): Collection
    {
        return $this->characterPositions;
    }

    public function addCharacterPosition(CharacterPosition $characterPosition): static
    {
        if (!$this->characterPositions->contains($characterPosition)) {
            $this->characterPositions->add($characterPosition);
            $characterPosition->setScene($this);
        }

        return $this;
    }

    public function removeCharacterPosition(CharacterPosition $characterPosition): static
    {
        if ($this->characterPositions->removeElement($characterPosition)) {
            // set the owning side to null (unless already changed)
            if ($characterPosition->getScene() === $this) {
                $characterPosition->setScene(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Action>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): static
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
            $action->setScene($this);
        }

        return $this;
    }

    public function removeAction(Action $action): static
    {
        if ($this->actions->removeElement($action)) {
            // set the owning side to null (unless already changed)
            if ($action->getScene() === $this) {
                $action->setScene(null);
            }
        }

        return $this;
    }
}
