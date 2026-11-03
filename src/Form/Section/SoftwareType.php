<?php

namespace App\Form\Section;

use App\Entity\Software;
use App\Form\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoftwareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Software name'])
            ->add('media', MediaType::class, [
                'label' => 'Logo or image',
                'mapped' => false,
                'required' => $options['image_required'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Software::class,
            'image_required' => true,
        ]);
        $resolver->setAllowedTypes('image_required', 'bool');
    }
}
