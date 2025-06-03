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

    #[ORM\ManyToOne(inversedBy: 'tarifications', cascade: ['persist'])]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'tarifications', cascade: ['persist'])]
    private ?ChargingStations $chargingStation = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $resalePrice = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $reducedPrice = null;

    #[ORM\Column(nullable: true)]
    private ?string $publicPrice = null;

    #[ORM\Column(nullable: true)]
    private ?string $rechargeTimeResale = null;

    #[ORM\Column(nullable: true)]
    private ?string $parkingTimeResale = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $OfferType = null;

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

    public function getPublicPrice(): ?string
    {
        return $this->publicPrice;
    }

    public function setPublicPrice(?string $publicPrice): static
    {
        $this->publicPrice = $publicPrice;

        return $this;
    }

    public function getRechargeTimeResale(): ?string
    {
        return $this->rechargeTimeResale;
    }

    public function setRechargeTimeResale(?string $rechargeTimeResale): static
    {
        $this->rechargeTimeResale = $rechargeTimeResale;

        return $this;
    }

    public function getParkingTimeResale(): ?string
    {
        return $this->parkingTimeResale;
    }

    public function setParkingTimeResale(?string $parkingTimeResale): static
    {
        $this->parkingTimeResale = $parkingTimeResale;

        return $this;
    }

    public function getOfferType(): ?string
    {
        return $this->OfferType;
    }

    public function setOfferType(?string $OfferType): static
    {
        $this->OfferType = $OfferType;

        return $this;
    }
}
