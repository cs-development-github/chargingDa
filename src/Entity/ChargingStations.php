<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\ChargingStationsRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ChargingStationsRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug est déjà utilisé.')]
#[ORM\HasLifecycleCallbacks]
class ChargingStations
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column]
    private ?int $connectors = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'chargingStations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'chargingStation')]
    private Collection $interventions;

    /**
     * @var Collection<int, Tarification>
     */
    #[ORM\OneToMany(targetEntity: Tarification::class, mappedBy: 'chargingStation')]
    private Collection $tarifications;

    /**
     * @var Collection<int, ChargingStationSetting>
     */
    #[ORM\OneToMany(targetEntity: ChargingStationSetting::class, mappedBy: 'chargingStation')]
    private Collection $chargingStationSettings;

    /**
     * @var Collection<int, ChargingStationDocumentation>
     */
    #[ORM\OneToMany(targetEntity: ChargingStationDocumentation::class, mappedBy: 'ChargingStation')]
    private Collection $chargingStationDocumentations;

    #[ORM\Column(nullable: true)]
    private ?int $power = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->tarifications = new ArrayCollection();
        $this->chargingStationSettings = new ArrayCollection();
        $this->chargingStationDocumentations = new ArrayCollection();
    }

    public function getDocumentations(): Collection
    {  
        return $this->chargingStationDocumentations;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getConnectors(): ?int
    {
        return $this->connectors;
    }

    public function setConnectors(int $connectors): static
    {
        $this->connectors = $connectors;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function generateSlug(SluggerInterface $slugger): void
    {
        $slug = $slugger->slug($this->model)->lower();
        $this->setSlug($slug);
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setChargingStation($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getChargingStation() === $this) {
                $intervention->setChargingStation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tarification>
     */
    public function getTarifications(): Collection
    {
        return $this->tarifications;
    }

    public function addTarification(Tarification $tarification): static
    {
        if (!$this->tarifications->contains($tarification)) {
            $this->tarifications->add($tarification);
            $tarification->setChargingStation($this);
        }

        return $this;
    }

    public function removeTarification(Tarification $tarification): static
    {
        if ($this->tarifications->removeElement($tarification)) {
            // set the owning side to null (unless already changed)
            if ($tarification->getChargingStation() === $this) {
                $tarification->setChargingStation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChargingStationSetting>
     */
    public function getChargingStationSettings(): Collection
    {
        return $this->chargingStationSettings;
    }

    public function addChargingStationSetting(ChargingStationSetting $chargingStationSetting): static
    {
        if (!$this->chargingStationSettings->contains($chargingStationSetting)) {
            $this->chargingStationSettings->add($chargingStationSetting);
            // $chargingStationSetting->setChargingStation($this);
        }

        return $this;
    }

    public function removeChargingStationSetting(ChargingStationSetting $chargingStationSetting): static
    {
        if ($this->chargingStationSettings->removeElement($chargingStationSetting)) {
            // set the owning side to null (unless already changed)
            if ($chargingStationSetting->getChargingStation() === $this) {
                $chargingStationSetting->setChargingStation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChargingStationDocumentation>
     */
    public function getChargingStationDocumentations(): Collection
    {
        return $this->chargingStationDocumentations;
    }

    public function addChargingStationDocumentation(ChargingStationDocumentation $chargingStationDocumentation): static
    {
        if (!$this->chargingStationDocumentations->contains($chargingStationDocumentation)) {
            $this->chargingStationDocumentations->add($chargingStationDocumentation);
            $chargingStationDocumentation->setChargingStation($this);
        }

        return $this;
    }

    public function removeChargingStationDocumentation(ChargingStationDocumentation $chargingStationDocumentation): static
    {
        if ($this->chargingStationDocumentations->removeElement($chargingStationDocumentation)) {
            // set the owning side to null (unless already changed)
            if ($chargingStationDocumentation->getChargingStation() === $this) {
                $chargingStationDocumentation->setChargingStation(null);
            }
        }

        return $this;
    }

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(?int $power): static
    {
        $this->power = $power;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
