<?php

namespace App\Form;

use App\Entity\Manufacturer;
use App\Entity\ChargingStations;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ChargingStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo de la borne',
                'attr' => ['class' => 'form-control'],
            ])    
            ->add('manufacturer', EntityType::class, [
                'class' => Manufacturer::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez un fabricant',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('connectors', IntegerType::class, [
                'label' => 'PDC',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('power', ChoiceType::class, [
                'label' => 'Puissance',
                'attr' => ['class' => 'form-control'],
                'choices' => array_combine(
                    array_map(fn($v) => $v . ' kW', range(3, 22)),
                    range(3, 22)
                ),
                'placeholder' => 'Sélectionnez la puissance',
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo de la borne',
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])            
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChargingStations::class,
        ]);
    }
}


