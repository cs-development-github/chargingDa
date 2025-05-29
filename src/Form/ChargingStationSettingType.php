<?php

namespace App\Form;

use App\Entity\ChargingStationSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChargingStationSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('public', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'data' => false,
                'label' => 'Mettre la borne en publique',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('addressLine', TextType::class, [
                'label' => 'Adresse de la borne',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal de la borne',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville de la borne',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('longitude', HiddenType::class)
            ->add('latitude', HiddenType::class)
            ->add('country', HiddenType::class)
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->add('region', HiddenType::class)
            ->add('department', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChargingStationSetting::class,
        ]);
    }
}

