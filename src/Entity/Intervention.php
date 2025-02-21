<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?Client $Client = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?User $installator = null;

    #[ORM\Column(length: 255)]
    private ?string $sim = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?ChargingStations $chargingStation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->Client;
    }

    public function setClient(?Client $Client): static
    {
        $this->Client = $Client;

        return $this;
    }

    public function getInstallator(): ?User
    {
        return $this->installator;
    }

    public function setInstallator(?User $installator): static
    {
        $this->installator = $installator;

        return $this;
    }

    public function getSim(): ?string
    {
        return $this->sim;
    }

    public function setSim(string $sim): static
    {
        $this->sim = $sim;

        return $this;
    }

    public function getChargingStation(): ?ChargingStations
    {
        return $this->chargingStation;
    }

    public function setChargingStation(?ChargingStations $chargingStation): static
    {
        $this->chargingStation = $chargingStation;

        return $this;
    }
}
