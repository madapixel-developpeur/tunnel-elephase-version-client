<?php

namespace App\Form;

use App\Entity\Blog;
use App\Entity\CategorieBlog;
use App\Entity\TagBlog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Repository\CategorieBlogRepository;
use App\Repository\TagBlogRepository;
use Symfony\Component\Validator\Constraints\File;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class BlogFormType extends AbstractType
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
            ->add('titre', TextType::class, [
                "label" => "Titre",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Titre obligatoire"])
                ]
            ])
            ->add('description', TextareaType::class,  [
                "label" => "Description",
                "trim" => true,
                "required" => false
            ])
            ->add('nomAuteur', TextType::class, [
                "label" => "Auteur",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Auteur obligatoire"])
                ]
            ])
            ->add('categorie', EntityType::class, [
                "label" => "Catégorie",
                'class'=> CategorieBlog::class,
                'choices' => $categoryList,
                'choice_label' => function(?CategorieBlog $category) {
                    return $category ? strtoupper($category->getNom()) : '';
                },
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Catégorie obligatoire"])
                ]
            ])
            ->add('contenu', CKEditorType::class,  array(
                //'label' => 'Contenu',
                'config' => array(
                    'uiColor' => '#ffffff',
                ),
                //'required' => true
            ))
            ->add('imageFile', FileType::class, [
                "label" => "Image d'illustration",
                'mapped' => false,
                "required" => false,
                'constraints' => [
                    new File([
                        // 'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Image invalide. Le format doit être: .jpeg ou .png',
                    ])
                ]
            ])
            ->add('tagsArray', EntityType::class, [
                "label" => "Tags",
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
