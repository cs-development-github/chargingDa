<?php

namespace App\Entity;

use App\Repository\ChargingStationDocumentationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChargingStationDocumentationRepository::class)]
class ChargingStationDocumentation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ocpp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $napn = null;

    #[ORM\ManyToOne(inversedBy: 'chargingStationDocumentations')]
    private ?ChargingStations $ChargingStation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getOcpp(): ?string
    {
        return $this->ocpp;
    }

    public function setOcpp(?string $ocpp): static
    {
        $this->ocpp = $ocpp;

        return $this;
    }

    public function getNapn(): ?string
    {
        return $this->napn;
    }

    public function setNapn(?string $napn): static
    {
        $this->napn = $napn;

        return $this;
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
}
