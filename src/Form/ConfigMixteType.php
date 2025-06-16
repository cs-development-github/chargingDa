<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigMixteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prix_collab', NumberType::class, [
                'label' => 'Prix :',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'styled-input'],
            ])
            ->add('prix_public', NumberType::class, [
                'label' => 'Prix : ',
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'styled-input'],
            ])
            ->add('cout_minute', NumberType::class, [
                'label' => false,
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'styled-input'],
            ])
            ->add('penalite', NumberType::class, [
                'label' => false,
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'styled-input'],
            ])
             ->add('prix_collab_dc', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Tarif collaborateurs DC'
            ])
            ->add('prix_public_dc', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Tarif public DC'
            ])
            ->add('cout_minute_dc', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Coût à la minute DC'
            ])
            ->add('penalite_dc', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Pénalité post-recharge DC'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}