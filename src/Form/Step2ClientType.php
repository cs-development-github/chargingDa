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
            ->add('name', TextType::class)
            ->add('lastname', TextType::class)
            ->add('societyName', TextType::class)
            ->add('siret', TextType::class, ['required' => false])
            ->add('numberTva', TextType::class, ['required' => false])
            ->add('codeNaf', TextType::class, ['required' => false])
            ->add('email', EmailType::class)
            ->add('phone', TelType::class, ['required' => false])
            ->add('legalForm', ChoiceType::class, [
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
