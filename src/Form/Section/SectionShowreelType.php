<?php

    namespace App\Form\Section;

    use App\Entity\Section;
    use App\Form\MediaType;
    use Symfony\Component\Form\AbstractType;
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
