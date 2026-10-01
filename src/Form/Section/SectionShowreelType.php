<?php

    namespace App\Form\Section;

    use App\Entity\Section;
    use App\Form\MediaType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                    'label' => 'Image',
                ]);
        }

        public function configureOptions(
            OptionsResolver $resolver
        ): void {
            $resolver->setDefaults([
                'data_class' => Section::class,
            ]);
        }
    }
