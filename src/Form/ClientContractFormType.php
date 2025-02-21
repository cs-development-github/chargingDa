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

class ClientContractFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire']),
                ],
            ])
            ->add('societyName', TextType::class, [
                'label' => 'Entreprise',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire']),
                ],
            ])
            ->add('siret', TextType::class, [
                'label' => 'Siret',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('numberTva', TextType::class, [
                'label' => 'Numéro de TVA',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('codeNaf', TextType::class, [
                'label' => 'Code NAF',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priceKwh', TextType::class, [
                'label' => 'Prix d\'achat kWh',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priceResale', TextType::class, [
                'label' => 'Prix de revente kWh',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse de facturation',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('legalForm', ChoiceType::class, [
                'label' => 'Forme juridique',
                'choices' => [
                    'SARL' => 'SARL',
                    'SAS' => 'SAS',
                    'EI' => 'EI',
                    'EURL' => 'EURL',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner la forme juridique']),
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
