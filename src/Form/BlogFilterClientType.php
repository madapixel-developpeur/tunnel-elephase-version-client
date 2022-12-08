<?php

namespace App\Form;

use App\Entity\CategorieBlog;
use App\Entity\TagBlog;
use App\Repository\CategorieBlogRepository;
use App\Repository\TagBlogRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BlogFilterClientType extends AbstractType
{
    private $categorieBlogRepository;
    private $tagBlogRepository;


    public function __construct(CategorieBlogRepository $categorieBlogRepository, TagBlogRepository $tagBlogRepository){
        $this->categorieBlogRepository = $categorieBlogRepository;
        $this->tagBlogRepository = $tagBlogRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryList = $this->categorieBlogRepository->getValidCategories();
        $tagList = $this->tagBlogRepository->getValidTags();
        
        $builder
        ->add('search', TextType::class, [
            "label" => false,
            "trim" => true,
            "required" => false
        ])
        
        ->add('categorie', EntityType::class, [
            "label" => false,
            'class'=> CategorieBlog::class,
            'choices' => $categoryList,
            'choice_label' => function(?CategorieBlog $category) {
                return $category ? $category->getNom() : '';
            },
            "required" => false
        ])
        ->add('tags', EntityType::class, [
            "label" => false,
            'class'=> TagBlog::class,
            'choices' => $tagList,
            'choice_label' => function(?TagBlog $tag) {
                return $tag ? $tag->getNom() : '';
            },
            'multiple' => true,
            'expanded' => true,
            "required" => false,
            'placeholder' => false
        ])
        ;

       

    }

    
}
