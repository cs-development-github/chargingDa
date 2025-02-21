<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TarificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('priceKwh', NumberType::class, [
                'label' => "Prix d'achat kWh",
                'required' => true,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priceResale', NumberType::class, [
                'label' => "Prix de revente kWh",
                'required' => true,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}