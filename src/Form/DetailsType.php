<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Details;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', null, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'nom', // Affiche le nom de l'article
                'label' => 'Article',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Details::class,
        ]);
    }
}

