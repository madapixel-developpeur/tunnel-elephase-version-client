<?php

namespace App\Entity;

use App\Repository\InventaireMereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:InventaireMereRepository::class)]
class InventaireMere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private $id;

    #[ORM\Column(type:"datetime")]
    private $dateInventaire;

    #[ORM\Column(type:"text", nullable:true)]
    private $description;

    #[ORM\Column(type:"integer")]
    private $statut;

    #[ORM\OneToMany(targetEntity:InventaireFille::class, mappedBy:"mere")]
    private $inventaireFilles;

    public function __construct()
    {
        $this->inventaireFilles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateInventaire(): ?\DateTimeInterface
    {
        return $this->dateInventaire;
    }

    public function setDateInventaire(\DateTimeInterface $dateInventaire): self
    {
        $this->dateInventaire = $dateInventaire;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, InventaireFille>
     */
    public function getInventaireFilles(): Collection
    {
        return $this->inventaireFilles;
    }

    public function addInventaireFille(InventaireFille $inventaireFille): self
    {
        if (!$this->inventaireFilles->contains($inventaireFille)) {
            $this->inventaireFilles[] = $inventaireFille;
            $inventaireFille->setMere($this);
        }

        return $this;
    }

    public function removeInventaireFille(InventaireFille $inventaireFille): self
    {
        if ($this->inventaireFilles->removeElement($inventaireFille)) {
            // set the owning side to null (unless already changed)
            if ($inventaireFille->getMere() === $this) {
                $inventaireFille->setMere(null);
            }
        }

        return $this;
    }

    public function initFilles(int $count){
        for($i=0; $i<$count; $i++){
            $fille = new InventaireFille();
            $fille->setStatut(1);
            $this->addInventaireFille($fille);
        }
    }

    /**
     * Set the value of inventaireFilles
     *
     * @return  self
     */ 
    public function setInventaireFilles($inventaireFilles)
    {
        foreach($inventaireFilles as $fille){
            $fille->setProduitlib($fille->getProduit()->getName());
        }
        $this->inventaireFilles = $inventaireFilles;

        return $this;
    }
}
