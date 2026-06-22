<?php

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $context = null;

    #[ORM\ManyToOne(inversedBy: 'actions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Scene $scene = null;

    #[ORM\OneToMany(mappedBy: 'action', targetEntity: Intervenes::class, orphanRemoval: true)]
    private Collection $intervenes;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'action', targetEntity: TakesPlace::class, orphanRemoval: true)]
    private Collection $takesPlaces;

    public function __construct()
    {
        $this->intervenes = new ArrayCollection();
        $this->takesPlaces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(string $context): static
    {
        $this->context = $context;

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
            $intervene->setAction($this);
        }

        return $this;
    }

    public function removeIntervene(Intervenes $intervene): static
    {
        if ($this->intervenes->removeElement($intervene)) {
            // set the owning side to null (unless already changed)
            if ($intervene->getAction() === $this) {
                $intervene->setAction(null);
            }
        }

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
            $takesPlace->setAction($this);
        }

        return $this;
    }

    public function removeTakesPlace(TakesPlace $takesPlace): static
    {
        if ($this->takesPlaces->removeElement($takesPlace)) {
            // set the owning side to null (unless already changed)
            if ($takesPlace->getAction() === $this) {
                $takesPlace->setAction(null);
            }
        }

        return $this;
    }
}
