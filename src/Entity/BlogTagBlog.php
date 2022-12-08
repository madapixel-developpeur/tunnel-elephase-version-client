<?php

namespace App\Entity;

use App\Repository\BlogTagBlogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogTagBlogRepository::class)]
class BlogTagBlog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Blog::class, inversedBy: "tags")]
    #[ORM\JoinColumn(nullable: false)]
    private $blog;

    #[ORM\ManyToOne(targetEntity: TagBlog::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $tag;

    #[ORM\Column(type: "integer")]
    private $statut;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): self
    {
        $this->blog = $blog;

        return $this;
    }

    public function getTag(): ?TagBlog
    {
        return $this->tag;
    }

    public function setTag(?TagBlog $tag): self
    {
        $this->tag = $tag;

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

    public static function generateTagIdArray($tagArray){
        $result = [];
        foreach($tagArray as $tag){
            $result[] = $tag->getId();
        }
        return $result;
    }
}
