<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class Step2ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],

            ])
            ->add('lastname', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control'],

            ])
            ->add('societyName', TextType::class, [
                'label' => 'Société',
                'attr' => ['class' => 'form-control'],

            ])
            ->add('siret', TextType::class, [
                'label' => 'Numéro de siret',
                'attr' => ['class' => 'form-control'],

            ])
            ->add('numberTva', TextType::class, [
                'label' => "Numéro de TVA",
                'attr' => ['class' => 'form-control'],
            ])
            ->add('codeNaf', TextType::class, [
                'label' => 'Code NAF',
                'attr' => ['class' => 'form-control'],

            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['class' => 'form-control'],

            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('legalForm', ChoiceType::class, [
                'label' => 'Raison sociale',
                'attr' => ['class' => 'form-control'],
                'choices' => ['SAS' => 'SAS', 'SARL' => 'SARL', 'EI' => 'EI', 'Autre' => 'Autre'],
                'placeholder' => 'Sélectionner une forme',
                'required' => false
            ])
            ->add('address', AddressType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
