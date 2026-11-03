<?php

    namespace App\Form\Section;

    use App\Entity\Section;
    use App\Form\MediaType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\NumberType;
    use Symfony\Component\Validator\Constraints\Range;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class SectionShowreelType extends AbstractType
    {
        public function buildForm(
            FormBuilderInterface $builder,
            array $options
        ): void {
            $builder
                ->add('media', MediaType::class, [
                    'label' => 'Showreel video',
                    'required' => $options['media_required'],
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
                    'attr' => [
                        'min' => 0,
                        'max' => 120,
                        'step' => 0.1,
                    ],
                    'constraints' => [
                        new Range(min: 0, max: 120),
                    ],
                    'help' => 'Used when automatic hiding is enabled.',
                ]);
        }

        public function configureOptions(
            OptionsResolver $resolver
        ): void {
            $resolver->setDefaults([
                'data_class' => Section::class,
                'media_required' => true,
            ]);
            $resolver->setAllowedTypes('media_required', 'bool');
        }
    }
