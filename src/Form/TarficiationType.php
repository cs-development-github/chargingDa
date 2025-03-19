<?php

namespace App\Form;

use App\Entity\ChargingStations;
use App\Entity\Client;
use App\Entity\Tarification;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TarficiationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('purcharsePrice')
            ->add('resalePrice')
            ->add('reducedPrice')
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'id',
            ])
            ->add('chargingStation', EntityType::class, [
                'class' => ChargingStations::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tarification::class,
        ]);
    }
}
