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
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?User $installator = null;

    #[ORM\Column(length: 255)]
    private ?string $sim = null;

    #[ORM\ManyToOne(targetEntity: ChargingStations::class, cascade: ['persist'])]
    private ?ChargingStations $chargingStation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $borneName = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $reference = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

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

    public function getBorneName(): ?string
    {
        return $this->borneName;
    }

    public function setBorneName(string $borneName): static
    {
        $this->borneName = $borneName;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }
}
