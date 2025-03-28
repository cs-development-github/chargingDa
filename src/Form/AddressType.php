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
            ->add('streetNumber', HiddenType::class, ['required' => true])
            ->add('streetName', HiddenType::class, ['required' => true])
            ->add('postalCode', HiddenType::class, ['required' => true])
            ->add('city', HiddenType::class, ['required' => true])
            ->add('country', HiddenType::class, ['required' => true, 'data' => 'France'])
            ->add('latitude', HiddenType::class, ['required' => true])
            ->add('longitude', HiddenType::class, ['required' => true])
            ->add('region', HiddenType::class, ['required' => true])
            ->add('department', HiddenType::class, ['required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Address::class]);
    }
}