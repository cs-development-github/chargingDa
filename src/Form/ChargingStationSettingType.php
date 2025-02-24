<?php

namespace App\Form;

use App\Entity\ChargingStations;
use App\Entity\ChargingStationSetting;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChargingStationSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('public')
            ->add('adress')
            ->add('installedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('supervisedAt', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChargingStationSetting::class,
        ]);
    }
}
