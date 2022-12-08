<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Gregwar\CaptchaBundle\Type\CaptchaType;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe', 
                    'constraints' => [
                        new NotBlank(["message" => "Nouveau mot de passe obligatoire"])
                    ]
                ],
                'second_options' => array('label' => 'Confirmer le nouveau mot de passe'),
                'required' => true,
                'invalid_message' => 'Mots de passe non conformes'
            ])
            ->add('currentPassword', PasswordType::class, [
                "label" => "Mot de passe actuel",
                "required" => true,
                'constraints' => [
                    new NotBlank(["message" => "Mot de passe actuel obligatoire"])
                ]
            ])
            ->add('captcha', CaptchaType::class, array(
                "label" => "Code visuel - Captcha: ",
                'width' => 200,
                'height' => 50,
                'length' => 6,
                // 'as_url' => true,
                // 'reload'=>true
                'distortion' => false
            ));;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
