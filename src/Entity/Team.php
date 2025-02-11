<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'team')]
    private Collection $user;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    private ?User $createdBy = null;
    
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, SimCard>
     */
    #[ORM\OneToMany(targetEntity: SimCard::class, mappedBy: 'team')]
    private Collection $simCards;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->simCards = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setTeam($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getTeam() === $this) {
                $user->setTeam(null);
            }
        }

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

    public function getChefEffectif(): ?User
    {
        foreach ($this->user as $installateur) {
            if ($installateur->isChefEffectif()) {
                return $installateur;
            }
        }
        return null;
    }

    public function getInstallator(EntityManagerInterface $entityManagerInterface): array
    {
        return $entityManagerInterface->getRepository(User::class)->findBy(['team' => $this]);
    }

    public function getSimCard(EntityManagerInterface $entityManagerInterface): array
    {
        return $entityManagerInterface->getRepository(User::class)->findBy(['team' => $this]);
    }


    /**
     * @return Collection<int, SimCard>
     */
    public function getSimCards(): Collection
    {
        return $this->simCards;
    }

    public function addSimCard(SimCard $simCard): static
    {
        if (!$this->simCards->contains($simCard)) {
            $this->simCards->add($simCard);
            $simCard->setTeam($this);
        }
        return $this;
    }

    public function removeSimCard(SimCard $simCard): static
    {
        if ($this->simCards->removeElement($simCard)) {
            if ($simCard->getTeam() === $this) {
                $simCard->setTeam(null);
            }
        }
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
        $slug = $slugger->slug($this->name)->lower();
        $this->setSlug($slug);
    }
}
