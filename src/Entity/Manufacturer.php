<?php

namespace App\Entity;

use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
class Manufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    /**
     * @var Collection<int, ChargingStations>
     */
    #[ORM\OneToMany(targetEntity: ChargingStations::class, mappedBy: 'manufacturer')]
    private Collection $chargingStations;

    public function __construct()
    {
        $this->chargingStations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    /**
     * @return Collection<int, ChargingStations>
     */
    public function getChargingStations(): Collection
    {
        return $this->chargingStations;
    }

    public function addChargingStation(ChargingStations $chargingStation): static
    {
        if (!$this->chargingStations->contains($chargingStation)) {
            $this->chargingStations->add($chargingStation);
            $chargingStation->setManufacturer($this);
        }

        return $this;
    }

    public function removeChargingStation(ChargingStations $chargingStation): static
    {
        if ($this->chargingStations->removeElement($chargingStation)) {
            // set the owning side to null (unless already changed)
            if ($chargingStation->getManufacturer() === $this) {
                $chargingStation->setManufacturer(null);
            }
        }

        return $this;
    }
}
