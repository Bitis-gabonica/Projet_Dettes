<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15,unique: true)]
    private ?string $surname = null;

    #[ORM\Column(length: 10,unique: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100)]
    private ?string $adresse = null;

    #[ORM\OneToOne(inversedBy: 'client', cascade: ['persist', 'remove'])]
    private ?User $utilisateur = null;

    /**
     * @var Collection<int, Dette>
     */
    #[ORM\OneToMany(targetEntity: Dette::class, mappedBy: 'client')]
    private Collection $dettes;

    /**
     * @var Collection<int, DemandeDette>
     */
    #[ORM\OneToMany(targetEntity: DemandeDette::class, mappedBy: 'client')]
    private Collection $demandeDettes;

    public function __construct()
    {
        $this->dettes = new ArrayCollection();
        $this->demandeDettes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Dette>
     */
    public function getDettes(): Collection
    {
        return $this->dettes;
    }

    public function addDette(Dette $dette): static
    {
        if (!$this->dettes->contains($dette)) {
            $this->dettes->add($dette);
            $dette->setClient($this);
        }

        return $this;
    }

    public function removeDette(Dette $dette): static
    {
        if ($this->dettes->removeElement($dette)) {
            // set the owning side to null (unless already changed)
            if ($dette->getClient() === $this) {
                $dette->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DemandeDette>
     */
    public function getDemandeDettes(): Collection
    {
        return $this->demandeDettes;
    }

    public function addDemandeDette(DemandeDette $demandeDette): static
    {
        if (!$this->demandeDettes->contains($demandeDette)) {
            $this->demandeDettes->add($demandeDette);
            $demandeDette->setClient($this);
        }

        return $this;
    }

    public function removeDemandeDette(DemandeDette $demandeDette): static
    {
        if ($this->demandeDettes->removeElement($demandeDette)) {
            // set the owning side to null (unless already changed)
            if ($demandeDette->getClient() === $this) {
                $demandeDette->setClient(null);
            }
        }

        return $this;
    }
}
