<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ContactUsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('lastname', TextType::class, [
            "label" => "Nom",
            "trim" => true,
            "required" => false,
            'attr' => [
                'placeholder' => 'Nom'
            ],
            "constraints" => [
                new NotBlank(["message" => "Veuillez mentionner votre nom"])
            ]
        ])
        ->add('firstname', TextType::class, [
            "label" => "Prénom",
            "trim" => true,
            "required" => false,
            'attr' => [
                'placeholder' => 'Prénom'
            ],
            "constraints" => [
                new NotBlank(["message" => "Veuillez mentionner votre prénom"])
            ]
        ])
        ->add('email', EmailType::class, [
            "label" => "E-mail",
            "trim" => true,
            "required" => false,
            'attr' => [
                'placeholder' => 'E-mail'
            ],
            "constraints" => [
                new NotBlank(["message" => "Email obligatoire"])
            ]
        ])
        ->add('company', TextType::class, [
            "label" => "Entreprise",
            "trim" => true,
            "required" => false,
            'attr' => [
                'placeholder' => 'Entreprise'
            ],
            "constraints" => [
                new NotBlank(["message" => "Veuillez mentionner votre entreprise"])
            ]
        ])
        ->add('fonction', TextType::class, [
            "label" => "Fonction",
            "trim" => true,
            "required" => false,
            'attr' => [
                'placeholder' => 'Fonction'
            ],
            "constraints" => [
                new NotBlank(["message" => "Veuillez mentionner votre fonction actuelle"])
            ]
        ])
        ->add('phone', TextType::class, [
            "label" => "Téléphone",
            "trim" => true,
            "required" => false,
            'attr' => [
                'placeholder' => 'Téléphone'
            ],
            "constraints" => [
                new NotBlank(["message" => "Veuillez mentionner votre téléphone"])
            ]
        ])
        ->add('message', TextareaType::class, [
            "label" => "Message",
            "trim" => true,
            'attr' => [
                'placeholder' => 'Contenu du message'
            ],
            "required" => false,
            "constraints" => [
                new NotBlank(["message" => "Votre message est vide"])
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
