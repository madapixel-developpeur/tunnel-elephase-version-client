<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class InventaireFilterType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
        ->add('description', TextType::class, [
            "label" => "Description",
            "required" => false,
            "trim" => true
        ])
        ->add('sort', ChoiceType::class, [
            "label" => "Trier par",
            'choices'  => [
                'Date' => "i.dateInventaire",
                'Description' => "i.description",
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

        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
