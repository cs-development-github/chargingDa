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
    private ?ChargingStations $ChargingStation = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?User $installer = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?SimCard $sim = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChargingStation(): ?ChargingStations
    {
        return $this->ChargingStation;
    }

    public function setChargingStation(?ChargingStations $ChargingStation): static
    {
        $this->ChargingStation = $ChargingStation;

        return $this;
    }

    public function getInstaller(): ?User
    {
        return $this->installer;
    }

    public function setInstaller(?User $installer): static
    {
        $this->installer = $installer;

        return $this;
    }

    public function getSim(): ?SimCard
    {
        return $this->sim;
    }

    public function setSim(SimCard $sim): static
    {
        $this->sim = $sim;

        return $this;
    }

}
