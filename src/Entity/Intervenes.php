<?php

namespace App\Entity;

use App\Repository\IntervenesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: IntervenesRepository::class)]
class Intervenes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $positionId = null;

    #[ORM\Column(length: 255)]
    private ?string $context = null;

    #[ORM\ManyToOne(inversedBy: 'intervenes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?HistoricCharacter $historicCharacter = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'intervenes')]
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

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(string $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getHistoricCharacter(): ?HistoricCharacter
    {
        return $this->historicCharacter;
    }

    public function setHistoricCharacter(?HistoricCharacter $historicCharacter): static
    {
        $this->historicCharacter = $historicCharacter;

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
