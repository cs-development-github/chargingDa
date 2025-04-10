<?php
namespace App\Form;

use App\Entity\ChargingStations;
use App\Entity\Intervention;
use App\Form\DataTransformer\ChargingStationToIdTransformer;
use App\Repository\ChargingStationsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SingleInterventionType extends AbstractType
{

    public function __construct(private ChargingStationsRepository $chargingStationsRepository)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sim', TextType::class, [
                'label' => 'Numéro de SIM',
            ])
            ->add('chargingStation', HiddenType::class, [
                'attr' => [
                    'id' => 'modelField',
                    'class' => 'charging-station-field'
                ],
                'mapped' => true,
            ])
            ->add('borneName', TextType::class, [
                'label' => 'Identifiant de la borne'
            ]);

            $builder->get('chargingStation')->addModelTransformer(
                new ChargingStationToIdTransformer($this->chargingStationsRepository)
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
