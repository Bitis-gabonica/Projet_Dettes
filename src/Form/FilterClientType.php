<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Numéro de téléphone',
                ],
                'required' => false, // Permettre que ce champ soit vide
            ])
            ->add('createUser', ChoiceType::class, [
                'choices' => [
                    'Tout' => null,
                    'Utilisateur' => true,
                    'Non utilisateur' => false,
                ],
                'required' => false, // Permettre que ce champ soit vide
            ])
            ->add('recherche', SubmitType::class, [
                'label' => 'Rechercher',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            // Par exemple, si vous souhaitez utiliser un modèle spécifique
        ]);
    }
}
