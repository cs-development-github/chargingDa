<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigPubliqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $acCount = $options['ac_count'] ?? 0;
        $dcCount = $options['dc_count'] ?? 0;

        if ($acCount > 0) {
        $builder
            ->add('prix_collab', NumberType::class, [
                'label' => 'Pour les véhicules personnels (AC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
            ])
            ->add('prix_public', NumberType::class, [
                'label' => 'Pour les véhicules extérieurs (AC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
            ])
            ->add('cout_minute', NumberType::class, [
                'label' => 'Coût à la minute (AC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
            ])
            ->add('penalite', NumberType::class, [
                'label' => 'Pénalité après recharge (AC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
            ]);
        }
       if ($dcCount > 0) {
            $builder            ->add('prix_collab_dc', NumberType::class, [
                'label' => 'Pour les véhicules personnels (DC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
            ])
            ->add('prix_public_dc', NumberType::class, [
                'label' => 'Pour les véhicules extérieurs (DC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
            ])
            ->add('cout_minute_dc', NumberType::class, [
                'label' => 'Coût à la minute (DC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
            ])
            ->add('penalite_dc', NumberType::class, [
                'label' => 'Pénalité après recharge (DC)',
                'required' => false,
                'mapped' => false,
                'scale' => 2,
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
