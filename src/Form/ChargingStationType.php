<?php

namespace App\Form;

use App\Entity\Manufacturer;
use App\Entity\ChargingStations;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ChargingStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('manufacturer', EntityType::class, [
                'class' => Manufacturer::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez un fabricant',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('connectors', IntegerType::class, [
                'label' => 'Nombre de connecteurs',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('power', ChoiceType::class, [
                'label' => 'Puissance',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionnez la puissance',
                'choices' => [
                    '3,7 kW' => 3.7,
                    '7,4 kW' => 7.4,
                    '11 kW'  => 11,
                    '22 kW'  => 22,
                    '24 kW'  => 24,
                    '50 kW'  => 50,
                    '100 kW' => 100,
                    '150 kW' => 150,
                    '175 kW' => 175,
                    '200 kW' => 200,
                    '250 kW' => 250,
                    '300 kW' => 300,
                    '350 kW' => 350,
                ],
                'group_by' => function ($value, $key, $index) {
                    return $value <= 22 ? 'AC (Courant alternatif)' : 'DC (Courant continu)';
                },
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo de la borne',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('platform', ChoiceType::class, [
                'label' => 'Platforme pour la supervision',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionnez la plateforme',
                'choices' => [
                    'Téléphone' => 'phone',
                    'Ordinateur' => 'pc',
                ],
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficultés de la supervision',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Choisir la difficultés',
                'choices' => [
                    'Trés facile' => 'very-easy',
                    'Facile' => 'easy',
                    'Moyens' => 'mid',
                    'Difficile' => 'hard',
                    'Trés difficile' => 'very-hard',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Publiée la borne',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChargingStations::class,
        ]);
    }
}


