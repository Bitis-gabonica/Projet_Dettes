<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Dette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
            ->add('montant', null, [
                'label' => 'Montant total',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('montantVerser', null, [
                'label' => 'Montant versé',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('articles', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'nom', // Nom de l'article affiché
                'multiple' => true,
                'expanded' => false, // Affichage sous forme de liste déroulante
                'label' => 'Articles',
                'attr' => [
                    'class' => 'form-control select-articles',
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
