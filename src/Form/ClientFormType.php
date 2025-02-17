<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;

class ClientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'label' => 'EMAIL *',
            'attr' => ['placeholder' => 'EMAIL *', 'class' => 'form-control'],
            'constraints' => [
                new NotBlank(['message' => 'L\'email est obligatoire']),
            ],
        ])
        ->add('societyName', TextType::class, [
            'label' => 'ENTREPRISE *',
            'attr' => ['placeholder' => 'ENTREPRISE *', 'class' => 'form-control'],
            'constraints' => [
                new NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire']),
            ],
        ])
        ->add('name', TextType::class, [
            'label' => 'NOM',
            'attr' => ['placeholder' => 'NOM', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('lastname', TextType::class, [
            'label' => 'PRÉNOM',
            'attr' => ['placeholder' => 'PRÉNOM', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('siret', TextType::class, [
            'label' => 'SIRET',
            'attr' => ['placeholder' => 'SIRET', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('numberTva', TextType::class, [
            'label' => 'NUM TVA',
            'attr' => ['placeholder' => 'NUM TVA', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('codeNaf', TextType::class, [
            'label' => 'CODE NAF',
            'attr' => ['placeholder' => 'CODE NAF', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('phone', TextType::class, [
            'label' => 'TÉLÉPHONE',
            'attr' => ['placeholder' => 'TÉLÉPHONE', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('priceKwh', TextType::class, [
            'label' => 'PRIX ACHAT',
            'attr' => ['placeholder' => 'PRIX ACHAT', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('legalForm', ChoiceType::class, [
            'label' => 'Forme juridique',
            'choices' => [
                'Entreprise Individuelle (EI)' => 'EI',
                'Entreprise Individuelle à Responsabilité Limitée (EIRL)' => 'EIRL',
                'Entreprise Unipersonnelle à Responsabilité Limitée (EURL)' => 'EURL',
                'Société à Responsabilité Limitée (SARL)' => 'SARL',
                'Société Anonyme (SA)' => 'SA',
                'Société par Actions Simplifiée (SAS)' => 'SAS',
                'Société par Actions Simplifiée Unipersonnelle (SASU)' => 'SASU',
                'Société en Nom Collectif (SNC)' => 'SNC',
                'Société Coopérative de Production (SCOP)' => 'SCOP',
                'Société Coopérative d’Intérêt Collectif (SCIC)' => 'SCIC',
            ],
            'placeholder' => 'Sélectionnez une forme juridique',
            'attr' => ['class' => 'form-control']
        ])
        ->add('priceResale', TextType::class, [
            'label' => 'PRIX REVENTE',
            'attr' => ['placeholder' => 'PRIX REVENTE', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ])
        ->add('adress', TextType::class, [
            'label' => 'ADRESSE FACT',
            'attr' => ['placeholder' => 'ADRESSE FACT', 'class' => 'form-control'],
            'required' => false,
            'constraints' => [
                new Optional(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
