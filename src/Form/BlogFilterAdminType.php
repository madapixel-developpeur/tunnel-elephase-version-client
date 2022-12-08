<?php

namespace App\Form;

use App\Entity\CategorieBlog;
use App\Repository\CategorieBlogRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class BlogFilterAdminType extends AbstractType
{
    private $categorieBlogRepository;
    public function __construct(CategorieBlogRepository $categorieBlogRepository){
        $this->categorieBlogRepository = $categorieBlogRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryList = $this->categorieBlogRepository->getValidCategories();
        $builder
        ->add('titre', TextType::class, [
            "label" => "Titre",
            "trim" => true,
            "required" => false
        ])
        ->add('dateMin', DateTimeType::class, [
            "label" => "Date min",
            "required" => false,
            "input_format" => "d/m/Y H:i",
            "widget" => "single_text"
        ])
        ->add('dateMax', DateTimeType::class, [
            "label" => "Date max",
            "required" => false,
            "input_format" => "d/m/Y H:i",
            "widget" => "single_text"
        ])
        ->add('categorie', EntityType::class, [
            "label" => "Catégorie",
            'class'=> CategorieBlog::class,
            'choices' => $categoryList,
            'choice_label' => function(?CategorieBlog $category) {
                return $category ? strtoupper($category->getNom()) : '';
            },
            "required" => false
        ])
        ->add('sort', ChoiceType::class, [
            "label" => "Trier par",
            'choices'  => [
                'Date' => "b.datePublication",
                'Titre' => "b.titre",
                'Catégorie' => "c.nom"
            ],
            "required" => false
        ])
        ->add('direction', ChoiceType::class, [
            "label" => "Ordre",
            'choices'  => [
                'Croissant' => "asc",
                'Décroissant' => "desc"
            ],
            "required" => false
        ])
        ;

        if($options['admin']){
            $builder->add('nomAuteur', TextType::class, [
                "label" => "Auteur",
                "trim" => true,
                "required" => false
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['admin' => true]);
    }
}
