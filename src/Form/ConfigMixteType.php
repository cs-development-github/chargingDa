<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigMixteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prix_collab', NumberType::class, [
                'label' => 'Prix collaborateurs (€ HT / kWh)',
                'required' => true,
                'scale' => 2,
            ])
            ->add('prix_public', NumberType::class, [
                'label' => 'Prix public extérieur (€ HT / kWh)',
                'required' => false,
                'scale' => 2,
            ])
            ->add('cout_minute', NumberType::class, [
                'label' => 'Coût supplémentaire à la minute (€ HT)',
                'required' => false,
                'scale' => 2,
            ])
            ->add('penalite', NumberType::class, [
                'label' => 'Pénalité après recharge (€ HT)',
                'required' => false,
                'scale' => 2,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}