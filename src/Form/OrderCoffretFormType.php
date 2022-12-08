<?php

namespace App\Form;

use App\Entity\OrderCoffret;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class OrderCoffretFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $payment = $options['payment'];
        $builder
            ->add('qteCoffret', IntegerType::class, [
                'label' => 'Quantité',
                'required' => true,
                'trim' => true,
                "constraints" => [
                    new Positive(["message" => "Quantité invalide"])
                ]
            ])
            ->add('info', InfoOrderCoffretFormType::class, [
                'label' => false,
                'required' => true,
            ]);  
        
        if($payment){
            $builder
                ->add('token', HiddenType::class, [
                    'required' => true,
                    'mapped' => false,
                    'constraints' => [new NotBlank()],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => OrderCoffret::class, 'payment' => false]);
    }
}
