<?php

namespace App\Form;

use App\Entity\InfoOrderCoffret;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class InfoOrderCoffretFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $builder
            ->add('nom', TextType::class, [
                "label" => "Nom *",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Nom obligatoire"])
                ]
            ])
            ->add('prenom', TextType::class, [
                "label" => "Prénom *",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Prénom obligatoire"])
                ]
            ])
            ->add('nomEntreprise', TextType::class, [
                "label" => "Nom de l’entreprise (facultatif)",
                "trim" => true,
                "required" => false,
            ])
            ->add('paysRegion', TextType::class, [
                "label" => "Pays/région *",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Pays/région obligatoire"])
                ]
            ])
            ->add('rue', TextType::class, [
                "label" => "Numéro et nom de rue *",
                "trim" => true,
                "required" => true,
                "attr" => ["placeholder" => "Numéro et nom de rue"],
                "constraints" => [
                    new NotBlank(["message" => "Numéro et nom de rue obligatoires"])
                ]
            ])
            ->add('lot', TextType::class, [
                "label" => false,
                "trim" => true,
                "required" => false,
                "attr" => ["placeholder" => "Bâtiment, appartement, lot, etc. (facultatif)"],  
            ])
            ->add('ville', TextType::class, [
                "label" => "Ville *",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Ville obligatoire"])
                ]
            ])
            ->add('provinceDepartement', TextType::class, [
                "label" => "Province/département *",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Province/département obligatoire"])
                ]
            ])
            ->add('telephone', TextType::class, [
                "label" => "Téléphone *",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Téléphone obligatoire"])
                ]
            ])
            ->add('email', TextType::class, [
                "label" => "E-mail *",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "E-mail obligatoire"])
                ]
            ])
            ->add('notes', TextareaType::class,  [
                "label" => "Notes (facultatif)",
                "required" => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InfoOrderCoffret::class,
            'readonly' => false
        ]);
    }
}
