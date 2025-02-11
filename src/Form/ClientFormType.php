<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'EMAIL *'])
            ->add('societyName', TextType::class, ['label' => 'ENTREPRISE *'])
            ->add('name', TextType::class, ['label' => 'NOM'])
            ->add('lastname', TextType::class, ['label' => 'PRÉNOM'])
            ->add('siret', TextType::class, ['label' => 'SIRET'])
            ->add('numberTva', TextType::class, ['label' => 'NUM TVA'])
            ->add('codeNaf', TextType::class, ['label' => 'CODE NAF'])
            ->add('phone', TextType::class, ['label' => 'TÉLÉPHONE'])
            ->add('priceKwh', TextType::class, ['label' => 'PRIX ACHAT'])
            ->add('priceResale', TextType::class, ['label' => 'PRIX REVENTE'])
            ->add('adress', TextType::class, ['label' => 'ADRESSE FACT'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
