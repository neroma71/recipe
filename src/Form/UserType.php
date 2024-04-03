<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('fullName', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minlength' => '2',
                'maxlength' => '50'
            ],
            'label' => 'Nom / Prenom',
            'label_attr' => [
                'class' => 'form_label mt-4 mb-2'
            ]
        ])
        ->add('pseudo', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minlength' => '2',
                'maxlength' => '50'
            ],
            'label' => 'pseudo',
            'label_attr' => [
                'class' => 'form_label mt-4 mb-2'
            ]
        ])
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'required' => false, // Rend le champ facultatif
            'invalid_message' => 'Les champs de mot de passe doivent correspondre.',
            'first_options'  => [
                'label' => 'Nouveau mot de passe',
                'label_attr' => ['class' => 'col-form-label mt-4'],
                'attr' => ['class' => 'form-control']
            ],
            'second_options' => [
                'label' => 'Répéter le nouveau mot de passe',
                'label_attr' => ['class' => 'col-form-label mt-4'],
                'attr' => ['class' => 'form-control']
            ],
            'constraints' => [
                new Length([
                    'min' => 2,
                    'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-outline-secondary mt-4'
            ],
            'label' => 'Soumettre'
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
