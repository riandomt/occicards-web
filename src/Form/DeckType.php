<?php

namespace App\Form;

use App\Entity\Deck;
use App\Entity\Folder;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                    'class' => 'name',
                    'id' => 'name',
                    'placeholder' => 'mon-deck'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du deck',
                'attr' => [
                    'id' => 'description',
                    'placeholder' => 'cour de géographie'
                ]
            ])
            ->add('cards', TextType::class, [
                'required' => false,
                'attr' => [
                    'id' => 'cards',
                    'readonly' => true,
                ]
            ])

            ->add('parentId', HiddenType::class, [
                'mapped' => false
            ])
            ->add('userId', HiddenType::class, [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Deck::class,
        ]);
    }
}
