<?php

namespace App\Form;

use App\Entity\Dette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date de la dette',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('approvisionnements', CollectionType::class, [
                'entry_type' => ApprovisionnementType::class, // Utilisation d'un sous-formulaire pour les approvisionnements
                'allow_add' => true, // Permet l'ajout d'éléments dynamiquement
                'allow_delete' => true, // Permet la suppression d'éléments
                'by_reference' => false, // Nécessaire pour les collections dans Doctrine
                'attr' => [
                    'class' => 'form-collection',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dette::class,
        ]);
    }
}
