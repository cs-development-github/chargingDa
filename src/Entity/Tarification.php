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

    #[ORM\Column(nullable: true)]
    private ?string $publicPrice = null;

    // -------- Champs tarif public --------
    #[ORM\Column(nullable: true)]
    private ?string $fixedFeePublic = null;

    #[ORM\Column(nullable: true)]
    private ?string $rechargeTimePublic = null;

    #[ORM\Column(nullable: true)]
    private ?string $parkingTimePublic = null;

    // -------- Champs tarif préférentiel --------
    #[ORM\Column(nullable: true)]
    private ?string $fixedFeeResale = null;

    #[ORM\Column(nullable: true)]
    private ?string $rechargeTimeResale = null;

    #[ORM\Column(nullable: true)]
    private ?string $parkingTimeResale = null;

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

    public function getPublicPrice(): ?string
    {
        return $this->publicPrice;
    }

    public function setPublicPrice(?string $publicPrice): static
    {
        $this->publicPrice = $publicPrice;

        return $this;
    }

    public function getFixedFeePublic(): ?string
    {
        return $this->fixedFeePublic;
    }

    public function setFixedFeePublic(?string $fixedFeePublic): static
    {
        $this->fixedFeePublic = $fixedFeePublic;

        return $this;
    }

    public function getRechargeTimePublic(): ?string
    {
        return $this->rechargeTimePublic;
    }

    public function setRechargeTimePublic(?string $rechargeTimePublic): static
    {
        $this->rechargeTimePublic = $rechargeTimePublic;

        return $this;
    }

    public function getParkingTimePublic(): ?string
    {
        return $this->parkingTimePublic;
    }

    public function setParkingTimePublic(?string $parkingTimePublic): static
    {
        $this->parkingTimePublic = $parkingTimePublic;

        return $this;
    }

    public function getFixedFeeResale(): ?string
    {
        return $this->fixedFeeResale;
    }

    public function setFixedFeeResale(?string $fixedFeeResale): static
    {
        $this->fixedFeeResale = $fixedFeeResale;

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
}
