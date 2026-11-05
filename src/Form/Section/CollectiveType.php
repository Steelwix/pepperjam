<?php

    namespace App\Form\Section;

    use App\Entity\Collective;
    use App\Entity\Section;
    use App\Form\MediaType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class CollectiveType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('media', MediaType::class, [
                    'required' => $options['image_required'],
                ])
                ->add('title', TextType::class)
                ->add('content', TextareaType::class, [
                    'attr' => [
                        'class' => 'trix-content',
                    ],
                ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults(['image_required' => true]);
            $resolver->setAllowedTypes('image_required', 'bool');
        }
    }
