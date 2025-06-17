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
        $acCount = $options['ac_count'] ?? 0;
        $dcCount = $options['dc_count'] ?? 0;

        if ($acCount > 0) {
            $builder
                ->add('prix_collab', NumberType::class, [
                    'label' => 'Prix collaborateurs AC (€HT/kWh)',
                    'required' => true,
                    'scale' => 2,
                    'attr' => ['class' => 'styled-input'],
                ])
                ->add('prix_public', NumberType::class, [
                    'label' => 'Prix public AC (€HT/kWh)',
                    'required' => false,
                    'scale' => 2,
                    'attr' => ['class' => 'styled-input'],
                ])
                ->add('cout_minute', NumberType::class, [
                    'label' => 'Coût à la minute AC',
                    'required' => false,
                    'scale' => 2,
                    'attr' => ['class' => 'styled-input'],
                ])
                ->add('penalite', NumberType::class, [
                    'label' => 'Pénalité post-recharge AC',
                    'required' => false,
                    'scale' => 2,
                    'attr' => ['class' => 'styled-input'],
                ]);
        }

        if ($dcCount > 0) {
            $builder
                ->add('prix_collab_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Tarif collaborateurs DC',
                    'attr' => ['class' => 'styled-input'],
                ])
                ->add('prix_public_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Tarif public DC',
                    'attr' => ['class' => 'styled-input'],
                ])
                ->add('cout_minute_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Coût à la minute DC',
                    'attr' => ['class' => 'styled-input'],
                ])
                ->add('penalite_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Pénalité post-recharge DC',
                    'attr' => ['class' => 'styled-input'],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'ac_count' => 0,
            'dc_count' => 0,
        ]);
    }
}
