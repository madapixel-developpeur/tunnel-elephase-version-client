<?php

namespace App\Entity;

use App\Repository\InventaireFilleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:InventaireFilleRepository::class)]
class InventaireFille
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity:InventaireMere::class, inversedBy:"inventaireFilles")]
    #[ORM\JoinColumn(nullable:false)]
    private $mere;

    #[ORM\ManyToOne(targetEntity:Product::class)]
    #[ORM\JoinColumn(nullable:false)]
    private $produit;

    #[ORM\Column(type:"integer")]
    private $qte;

    #[ORM\Column(type:"integer")]
    private $statut;

    private $produitlib;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMere(): ?InventaireMere
    {
        return $this->mere;
    }

    public function setMere(?InventaireMere $mere): self
    {
        $this->mere = $mere;

        return $this;
    }

    public function getProduit(): ?Product
    {
        return $this->produit;
    }

    public function setProduit(?Product $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getQte(): ?int
    {
        return $this->qte;
    }

    public function setQte(int $qte): self
    {
        $this->qte = $qte;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(?int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of produitlib
     */ 
    public function getProduitlib()
    {
        return $this->produitlib;
    }

    /**
     * Set the value of produitlib
     *
     * @return  self
     */ 
    public function setProduitlib($produitlib)
    {
        $this->produitlib = $produitlib;

        return $this;
    }
}
