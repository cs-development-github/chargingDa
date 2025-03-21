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

    /**
     * @var Collection<int, ChargingStations>
     */
    #[ORM\ManyToMany(targetEntity: ChargingStations::class, inversedBy: 'chargingStationDocumentations')]
    private Collection $chargingStation;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ocpp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $napn = null;

    public function __construct()
    {
        $this->chargingStation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ChargingStations>
     */
    public function getChargingStation(): Collection
    {
        return $this->chargingStation;
    }

    public function addChargingStation(ChargingStations $chargingStation): static
    {
        if (!$this->chargingStation->contains($chargingStation)) {
            $this->chargingStation->add($chargingStation);
        }

        return $this;
    }

    public function removeChargingStation(ChargingStations $chargingStation): static
    {
        $this->chargingStation->removeElement($chargingStation);

        return $this;
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
}
