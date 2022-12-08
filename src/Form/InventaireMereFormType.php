<?php

namespace App\Form;

use App\Entity\InventaireFille;
use App\Entity\InventaireMere;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\NotBlank;

class InventaireMereFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
           
            ->add('dateInventaire', DateTimeType::class, [
                'label' => "Date de l'inventaire",
                "input_format" => "d/m/Y H:i",
                "widget" => "single_text",
                'required' => true,
                'trim' => true,
                'constraints' => [
                    new NotBlank(["message" => "Date de l'inventaire obligatoire"])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'trim' => true
            ])
            ->add('inventaireFilles', CollectionType::class, [
                'label' => false,
                'entry_type' => InventaireFilleFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'prototype' => true,
                'allow_delete' => true,
                /*'delete_empty' => function (InventaireFille $fille = null){
                    return null === $fille || !($fille->getId() || $fille->getStatut() == 1);
                }*/
            ]);        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => InventaireMere::class]);
    }
}
