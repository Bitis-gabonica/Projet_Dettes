<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('surname',TextType::class,[

                'required'=>false
               
             ])
            ->add('telephone',TextType::class,[

                'required'=>false
               
             ])
            ->add('adresse',TextType::class,[

                'required'=>false
               
             ])
            ->add('createUser', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Creer un compte au client',
            ])
            ->add('newUser', UserType::class, [
                'mapped' => false,
                'required' => false,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
