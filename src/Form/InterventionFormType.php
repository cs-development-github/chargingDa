<?php

namespace App\Form;

use App\Entity\ChargingStations;
use App\Entity\Intervention;
use App\Entity\SimCard;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InterventionFormType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            ->add('sim', EntityType::class, [
                'class' => SimCard::class,
                'choice_label' => 'activate_code',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('s')
                        ->join('s.team', 't') // Supposons que SimCard a une relation `team`
                        ->where('t = :team')
                        ->setParameter('team', $user->getTeam()); // On récupère l'équipe de l'utilisateur
                },
            ])
            ->add('ChargingStation', EntityType::class, [
                'class' => ChargingStations::class,
                'choice_label' => 'model',
            ])
            ->add('installer', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
            $intervention = $event->getData();
            if ($intervention && null === $intervention->getInstaller()) {
                $intervention->setInstaller($user);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}