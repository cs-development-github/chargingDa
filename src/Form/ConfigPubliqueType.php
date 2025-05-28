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
        $builder
            ->add('prix_collab', NumberType::class, [
                'label' => 'Prix :',
                'required' => true,
                'scale' => 2,
            ])
            ->add('prix_public', NumberType::class, [
                'label' => 'Prix :',
                'required' => false,
                'scale' => 2,
            ])
            ->add('cout_minute', NumberType::class, [
                'label' => false,
                'required' => false,
                'scale' => 2,
            ])
            ->add('penalite', NumberType::class, [
                'label' => false,
                'required' => false,
                'scale' => 2,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}