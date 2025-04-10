<?php
namespace App\Form;

use App\Entity\ChargingStations;
use App\Entity\Intervention;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SingleInterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sim', TextType::class, [
                'label' => 'Numéro de SIM',
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de carte SIM est obligatoire.']),
                ],
            ])
            // ->add('chargingStation', EntityType::class, [
            //     'class' => ChargingStations::class,
            //     'label' => 'Borne de recharge',
            //     'choice_label' => 'model',
            //     'group_by' => function (ChargingStations $station) {
            //         return $station->getManufacturer()->getName();
            //     },
            //     'constraints' => [
            //         new NotBlank(['message' => 'La borne de recharge est obligatoire.']),
            //     ],
            // ])
            ->add('chargingStation', HiddenType::class, [
                'attr' => [
                    'id' => 'modelField',
                    'class' => 'charging-station-field'
                ],
            ])
            //rajoute l'id dans ca modelField
            ->add('borneName', TextType::class, [
                'label' => 'Identifiant de la borne'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
