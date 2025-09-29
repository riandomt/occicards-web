<?php

namespace App\Form;

use App\Entity\Deck;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du deck',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'deck_name',
                    'placeholder' => 'mon-deck',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du deck',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'deck_description',
                    'placeholder' => 'cours de géographie'
                ]
            ])
            ->add('cards', HiddenType::class, [
                'required' => false,
                'mapped' => true,
                'attr' => [
                    'id' => 'deck_cards',
                ],
            ])
            // si besoin de passer le dossier parent depuis le front
            ->add('folderId', HiddenType::class, [
                'mapped' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer un deck',
                'attr' => ['class' => 'btn btn-success mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Deck::class,
        ]);
    }
}
