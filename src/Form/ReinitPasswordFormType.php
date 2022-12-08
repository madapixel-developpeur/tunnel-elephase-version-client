<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReinitPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('verifCode', TextType::class, [
            "label"=>"Code de vérification", 
            "trim" => true, 
            "required" => true,
            "constraints" => [
                new NotBlank(["message" => "Code de vérification obligatoire"])
            ]
        ])
        ->add('newPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options'  => [
                'label' => 'Nouveau mot de passe',
                "constraints" => [
                    new NotBlank(["message" => "Nouveau mot de passe obligatoire"])
                ]
            ],
            'second_options' => array('label' => 'Confirmer le nouveau mot de passe'),
            'required' => true,
            'invalid_message' => 'Mots de passe non conformes'
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
