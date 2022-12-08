<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 500)]
    private $titre;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $image;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\Column(type: 'datetime')]
    private $datePublication;

    #[ORM\Column(type: 'string', length: 255)]
    private $nomAuteur;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $auteur;

    #[ORM\Column(type: "text")]
    private $contenu;

    #[ORM\ManyToOne(targetEntity: CategorieBlog::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $categorie;

    #[ORM\OneToMany(targetEntity: BlogTagBlog::class, mappedBy: "blog")]
    private $tags;

    #[ORM\Column(type: "integer")]
    private $statut;

    private $tagsArray;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

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

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeInterface $datePublication): self
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getNomAuteur(): ?string
    {
        return $this->nomAuteur;
    }

    public function setNomAuteur(string $nomAuteur): self
    {
        $this->nomAuteur = $nomAuteur;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCategorie(): ?CategorieBlog
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieBlog $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, BlogTagBlog>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(BlogTagBlog $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->setBlog($this);
        }

        return $this;
    }

    public function removeTag(BlogTagBlog $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getBlog() === $this) {
                $tag->setBlog(null);
            }
        }

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
     * Get the value of tagsArray
     */ 
    public function getTagsArray()
    {
        return $this->tagsArray;
    }

    /**
     * Set the value of tagsArray
     *
     * @return  self
     */ 
    public function setTagsArray($tagsArray)
    {
        $this->tagsArray = $tagsArray;

        return $this;
    }

    public function convertTagsToArray(){
        $tab = new ArrayCollection();
        for($i=0; $i<count($this->getTags()); $i++){
            $tab->add($this->getTags()[$i]->getTag());
        }
        $this->setTagsArray($tab);
    }

    public function findBlogTag(int $tagId){
        foreach($this->getTags() as $blogTag){
            if($blogTag->getTag()->getId() == $tagId) return $blogTag;
        }
        return null;
    }

    /**
     * Set the value of tags
     *
     * @return  self
     */ 
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function getConcatTags(){
        $concat = '';
        for($i=0; $i<count($this->getTags()); $i++){
            $concat .= $this->getTags()->get($i)->getTag()->getNom();
            if($i+1 < count($this->getTags())){
                $concat .= ', ';
            }
        }
        return $concat;
    }
}
