<?php

    namespace App\Form\Section;

    use App\Entity\Activity;
    use App\Form\MediaType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class ActivityType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('title', TextType::class, [
                    'label' => 'Title',
                ])
                ->add('media', MediaType::class, [
                    'label' => 'Media',
                    'mapped' => false,
                ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Activity::class,
            ]);
        }
    }
