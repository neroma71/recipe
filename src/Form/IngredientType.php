<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlenght' => '2',
                    'maxlength' => '255'
                ],
                'label' => 'Nom',
                'label_attr' =>[
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2, 'max' => 255]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('price', MoneyType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Prix ',
                'label_attr' =>[
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThan(200)
                ]
            ])
            ->add('images', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M', // Limite de poids à 2 méga-octets
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ], // Autoriser seulement les types d'images spécifiés
                        'mimeTypesMessage' => 'Please upload a valid JPG, PNG or GIF image', // Message d'erreur personnalisé
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'envoie'
                ],
                'label' => 'Créer mon ingrédient'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }
}
