<?php

namespace App\Form;

use App\Entity\ChargingStations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ChargingStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('manufacturer', TextType::class, [
                'label' => 'Fabricant',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('connectors', IntegerType::class, [
                'label' => 'PDC',
                'attr' => ['class' => 'form-control'],
            ])



            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif ',
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


