<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Intervention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientInterventionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', ClientFormType::class)
            ->add('intervention', InterventionFormType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas d'entité liée, on gère les deux objets séparément
        ]);
    }
}

?>