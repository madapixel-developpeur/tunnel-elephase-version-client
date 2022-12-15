<?php

namespace App\Entity;

use App\Repository\OrderCoffretRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderCoffretRepository::class)]
class OrderCoffret
{
    public const CREATED = 1;
    public const VALIDATED = 2;

    public const STATUS = [
        self::CREATED => "Créée",
        self::VALIDATED => "Acceptée"
    ];

    public const STATUS_DATA_FORM = [
        "Créée" => self::CREATED,
        "Acceptée" => self::VALIDATED
    ];
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coffret $coffret = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?InfoOrderCoffret $info = null;

    #[ORM\Column]
    private ?int $statut = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $prixCoffret = null;

    #[ORM\Column]
    private ?int $qteCoffret = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $montant = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $chargeId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $orderDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invoicePath = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoffret(): ?Coffret
    {
        return $this->coffret;
    }

    public function setCoffret(?Coffret $coffret): self
    {
        $this->coffret = $coffret;

        return $this;
    }

    public function getInfo(): ?InfoOrderCoffret
    {
        return $this->info;
    }

    public function setInfo(InfoOrderCoffret $info): self
    {
        $this->info = $info;

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

    public function getStatusStr(): ?string
    {
        return self::STATUS[$this->getStatut()];
    }

    public function getPrixCoffret(): ?string
    {
        return $this->prixCoffret;
    }

    public function setPrixCoffret(string $prixCoffret): self
    {
        $this->prixCoffret = $prixCoffret;

        return $this;
    }

    public function getQteCoffret(): ?int
    {
        return $this->qteCoffret;
    }

    public function setQteCoffret(int $qteCoffret): self
    {
        $this->qteCoffret = $qteCoffret;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    public function setChargeId(?string $chargeId): self
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getInvoicePath(): ?string
    {
        return $this->invoicePath;
    }

    public function setInvoicePath(?string $invoicePath): self
    {
        $this->invoicePath = $invoicePath;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva ?? 20.;
    }

    public function setTva(?float $tva): self
    {
        $this->tva = $tva;

        return $this;
    }
    
    public function getMontantTva(){
        return $this->getMontant() * $this->getTva() / (100 + $this->getTva());
    }

    public function getMontantHt(){
        return $this->getMontant() / (1. + $this->getTva()/100);
    }
}
