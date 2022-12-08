<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                "label" => "Nom",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Nom obligatoire"])
                ]
            ])
            ->add('nomUtilisateur', TextType::class, [
                "label" => "Nom d'utilisateur",
                "trim" => true,
                "required" => true,
                "constraints" => [
                    new NotBlank(["message" => "Nom d'utilisateur obligatoire"])
                ]
            ])
            ->add('prenom', TextType::class, [
                "label" => "Prénom",
                "trim" => true,
                "required" => false
            ])
            ->add('adresse', TextType::class, [
                "label" => "Adresse",
                "trim" => true,
                "required" => false
            ])
            ->add('numTel', TextType::class, [
                "label" => "Téléphone",
                "trim" => true,
                "required" => false
            ])
            ->add('captcha', CaptchaType::class, array(
                "label" => "Code visuel - Captcha: ",
                'width' => 200,
                'height' => 50,
                'length' => 6,
                // 'as_url' => true,
                // 'reload'=>true
                'distortion' => false
            ));
        ;

        if($options['signup']){
            $builder
            ->add('mail', EmailType::class, [
                "label" => "Adresse email",
                "trim" => true,
                "required" => true,
                'invalid_message' => 'Adresse email invalide',
                "constraints" => [
                    new NotBlank(["message" => "Adresse email obligatoire"])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => array('label' => 'Confirmer le mot de passe'),
                'required' => true,
                'invalid_message' => 'Mots de passe non conformes'
            ])
            ;
            
        } else {
            $builder
            ->add('imageFile', FileType::class, [
                "label" => "Changer la photo",
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
            ->add('removeImageFile', HiddenType::class, [
                "label" => false,
                "mapped" => false,
                "trim" => true,
                "required" => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'signup' => false
        ]);
    }
}
