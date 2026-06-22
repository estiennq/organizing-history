<?php

namespace App\Entity;

use App\Repository\HistoricCharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: HistoricCharacterRepository::class)]
class HistoricCharacter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $sprite = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'historicCharacter', targetEntity: Intervenes::class, orphanRemoval: true)]
    private Collection $intervenes;

    public function __construct()
    {
        $this->intervenes = new ArrayCollection();
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
     * @return Collection<int, Intervenes>
     */
    public function getIntervenes(): Collection
    {
        return $this->intervenes;
    }

    public function addIntervene(Intervenes $intervene): static
    {
        if (!$this->intervenes->contains($intervene)) {
            $this->intervenes->add($intervene);
            $intervene->setHistoricCharacter($this);
        }

        return $this;
    }

    public function removeIntervene(Intervenes $intervene): static
    {
        if ($this->intervenes->removeElement($intervene)) {
            // set the owning side to null (unless already changed)
            if ($intervene->getHistoricCharacter() === $this) {
                $intervene->setHistoricCharacter(null);
            }
        }

        return $this;
    }
}
