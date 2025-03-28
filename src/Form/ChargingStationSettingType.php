<?php

namespace App\Form;

use App\Entity\ChargingStations;
use App\Entity\ChargingStationSetting;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('streetNumber', HiddenType::class, ['required' => true])
            ->add('streetName', HiddenType::class, ['required' => true])
            ->add('postalCode', HiddenType::class, ['required' => true])
            ->add('city', HiddenType::class, ['required' => true])
            ->add('country', HiddenType::class, ['required' => true, 'data' => 'France'])
            ->add('latitude', HiddenType::class, ['required' => true])
            ->add('longitude', HiddenType::class, ['required' => true])
            ->add('region', HiddenType::class, ['required' => true])
            ->add('department', HiddenType::class, ['required' => true]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChargingStationSetting::class,
        ]);
    }
}
