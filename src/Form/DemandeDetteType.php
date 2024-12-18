<?php

namespace App\Form;

use App\Entity\DemandeDette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeDetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date de la Demande',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('details', CollectionType::class, [
                'entry_type' => DetailsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true, // Ajout du prototype
            ]);
            
            
            }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeDette::class,
        ]);
    }
}