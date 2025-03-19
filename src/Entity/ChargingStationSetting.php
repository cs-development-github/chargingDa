<?php

namespace App\Entity;

use App\Repository\ChargingStationSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChargingStationSettingRepository::class)]
class ChargingStationSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chargingStationSettings')]
    private ?ChargingStations $chargingStation = null;

    #[ORM\ManyToOne(inversedBy: 'chargingStationSettings')]
    private ?Client $client = null;

    #[ORM\Column(nullable: true)]
    private ?bool $public = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $installedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $supervisedAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): static
    {
        $this->public = $public;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getInstalledAt(): ?\DateTime
    {
        return $this->installedAt;
    }

    public function setInstalledAt(?\DateTime $installedAt): static
    {
        $this->installedAt = $installedAt;

        return $this;
    }

    public function getSupervisedAt(): ?\DateTime
    {
        return $this->supervisedAt;
    }

    public function setSupervisedAt(?\DateTime $supervisedAt): static
    {
        $this->supervisedAt = $supervisedAt;

        return $this;
    }
}
