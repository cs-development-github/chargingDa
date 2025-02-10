<?php

namespace App\Entity;

use App\Repository\SimCardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SimCardRepository::class)]
class SimCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $activateCode = null;

    #[ORM\ManyToOne(inversedBy: 'simCards')]
    private ?Team $team = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivateCode(): ?string
    {
        return $this->activateCode;
    }

    public function setActivateCode(string $activateCode): static
    {
        $this->activateCode = $activateCode;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }
}
