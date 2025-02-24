<?php

namespace App\Entity;

use App\Repository\TarificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarificationRepository::class)]
class Tarification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tarifications')]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'tarifications')]
    private ?ChargingStations $chargingStation = null;

    #[ORM\Column(length: 255)]
    private ?string $purcharsePrice = null;

    #[ORM\Column(length: 255)]
    private ?string $resalePrice = null;

    #[ORM\Column(length: 255)]
    private ?string $reducedPrice = null;

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

    public function getChargingStation(): ?ChargingStations
    {
        return $this->chargingStation;
    }

    public function setChargingStation(?ChargingStations $chargingStation): static
    {
        $this->chargingStation = $chargingStation;

        return $this;
    }

    public function getPurcharsePrice(): ?string
    {
        return $this->purcharsePrice;
    }

    public function setPurcharsePrice(string $purcharsePrice): static
    {
        $this->purcharsePrice = $purcharsePrice;

        return $this;
    }

    public function getResalePrice(): ?string
    {
        return $this->resalePrice;
    }

    public function setResalePrice(string $resalePrice): static
    {
        $this->resalePrice = $resalePrice;

        return $this;
    }

    public function getReducedPrice(): ?string
    {
        return $this->reducedPrice;
    }

    public function setReducedPrice(string $reducedPrice): static
    {
        $this->reducedPrice = $reducedPrice;

        return $this;
    }
}
