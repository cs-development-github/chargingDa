<?php

namespace App\Form;

use App\Entity\ChargingStationDocumentation;
use App\Entity\ChargingStations;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChargingStationsDocumentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class, [
                'label' => 'Image de la documentation',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Formats acceptés : JPEG, PNG, WebP',
                    ]),
                ],
            ])
            ->add('ocpp', TextType::class, [
                'label' => 'OCPP',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('napn', TextType::class, [
                'label' => 'NAPN',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('ChargingStation', EntityType::class, [
                'class' => ChargingStations::class,
                'choice_label' => 'model',
                'label' => 'Borne concernée',
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChargingStationDocumentation::class,
        ]);
    }
}
