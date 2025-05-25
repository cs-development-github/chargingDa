<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigFlotteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('termsAccepted', CheckboxType::class, [
                'label' => 'J’ai lu et j’accepte',
                'required' => true
            ]);
    }
}
