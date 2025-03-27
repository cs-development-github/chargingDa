<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullAddress', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'address-autocomplete',
                    'list' => 'address-suggestions',
                    'autocomplete' => 'off'
                ],
            ])
            ->add('streetNumber', HiddenType::class)
            ->add('streetName', HiddenType::class)
            ->add('postalCode', HiddenType::class)
            ->add('city', HiddenType::class)
            ->add('country', HiddenType::class, ['data' => 'France'])
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->add('region', HiddenType::class)
            ->add('department', HiddenType::class);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Address::class]);
    }
}