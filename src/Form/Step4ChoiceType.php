<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class Step4ChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => [
                'Bornes privées' => 'flotte',
                'Bornes publiques' => 'publique',
                'Bornes mixtes' => 'mixte',
            ],
            'expanded' => true,
            'multiple' => false,
            'label' => false,
        ]);
    }
}
