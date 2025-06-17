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
                    'label' => 'Pour les véhicules personnels (collaborateurs) :',
                    'required' => true,
                    'scale' => 2,
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 0.25'
                    ],
                ])
                ->add('prix_public', NumberType::class, [
                    'label' => 'Pour les véhicules extérieurs (public) :',
                    'required' => false,
                    'scale' => 2,
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 0.35'
                    ],
                ])
                ->add('cout_minute', NumberType::class, [
                    'label' => 'Coût supplémentaire à la minute :',
                    'required' => false,
                    'scale' => 2,
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 0.05'
                    ],
                ])
                ->add('penalite', NumberType::class, [
                    'label' => 'Pénalité après recharge (au-delà de 15 min) :',
                    'required' => false,
                    'scale' => 2,
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 2.00'
                    ],
                ]);
        }

        if ($dcCount > 0) {
            $builder
                ->add('prix_collab_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Pour les véhicules personnels (collaborateurs) :',
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 0.35'
                    ],
                ])
                ->add('prix_public_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Pour les véhicules extérieurs (public) :',
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 0.45'
                    ],
                ])
                ->add('cout_minute_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Coût supplémentaire à la minute :',
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 0.10'
                    ],
                ])
                ->add('penalite_dc', NumberType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Pénalité après recharge (au-delà de 15 min) :',
                    'attr' => [
                        'class' => 'styled-input',
                        'placeholder' => 'Ex : 3.00'
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ac_count' => 0,
            'dc_count' => 0,
        ]);
    }
}
