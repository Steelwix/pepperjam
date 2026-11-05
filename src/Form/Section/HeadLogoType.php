<?php

namespace App\Form\Section;

use App\Entity\Section;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Range;

class HeadLogoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('logo', FileType::class, [
            'label' => 'Logo image',
            'mapped' => false,
            'required' => $options['logo_required'],
            'constraints' => [
                new File([
                    'maxSize' => '10M',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                        'image/gif',
                        'image/webp',
                        'image/svg+xml',
                    ],
                    'mimeTypesMessage' => 'Upload a PNG, JPEG, GIF, WebP or SVG image.',
                ]),
            ],
        ])
            ->add('logoAutoHide', CheckboxType::class, [
                'label' => 'Hide the logo automatically',
                'required' => false,
                'mapped' => false,
            ])
            ->add('logoDisplayDuration', NumberType::class, [
                'label' => 'Logo display duration (seconds)',
                'required' => true,
                'mapped' => false,
                'scale' => 1,
                'html5' => true,
                'attr' => ['min' => 0, 'max' => 120, 'step' => 0.1],
                'constraints' => [new Range(min: 0, max: 120)],
                'help' => 'Used when automatic hiding is enabled.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Section::class,
            'logo_required' => true,
        ]);
        $resolver->setAllowedTypes('logo_required', 'bool');
    }
}
