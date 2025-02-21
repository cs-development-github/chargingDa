<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $societyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numberTva = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeNaf = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priceKwh = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priceResale = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    private ?string $secureToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legalForm = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    private ?User $createdBy = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Intervention::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $interventions;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
    }

    public function getSecureToken(): ?string
    {
        return $this->secureToken;
    }

    public function setSecureToken(?string $secureToken): self
    {
        $this->secureToken = $secureToken;
        return $this;
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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getSocietyName(): ?string
    {
        return $this->societyName;
    }

    public function setSocietyName(string $societyName): static
    {
        $this->societyName = $societyName;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getNumberTva(): ?string
    {
        return $this->numberTva;
    }

    public function setNumberTva(string $numberTva): static
    {
        $this->numberTva = $numberTva;

        return $this;
    }

    public function getCodeNaf(): ?string
    {
        return $this->codeNaf;
    }

    public function setCodeNaf(string $codeNaf): static
    {
        $this->codeNaf = $codeNaf;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPriceKwh(): ?string
    {
        return $this->priceKwh;
    }

    public function setPriceKwh(string $priceKwh): static
    {
        $this->priceKwh = $priceKwh;

        return $this;
    }

    public function getPriceResale(): ?string
    {
        return $this->priceResale;
    }

    public function setPriceResale(string $priceResale): static
    {
        $this->priceResale = $priceResale;

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

    public function getLegalForm(): ?string
    {
        return $this->legalForm;
    }

    public function setLegalForm(string $legalForm): static
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setClient($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            if ($intervention->getClient() === $this) {
                $intervention->setClient(null);
            }
        }

        return $this;
    }
}
