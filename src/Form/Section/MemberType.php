<?php

    namespace App\Form\Section;

    use App\Entity\Member;
    use App\Form\MediaType;
    use App\Form\Section\MemberData\LinkMemberDataType;
    use App\Form\Section\MemberData\MemberDataType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class MemberType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('firstname', TextType::class, [
                    'label' => 'Prénom',
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Nom',
                    'required' => false,
                ])
                ->add('bio',  TextareaType::class, [
                    'attr' => [
                        'class' => 'trix-content',
                        'hidden' => true
                    ],
                ])
                ->add('media', MediaType::class, [
                    'label' => 'Image',
                    'mapped' => false,
                ])
                ->add('links', CollectionType::class, [
                    'entry_type' => LinkMemberDataType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'mapped' => false,
                ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Member::class,
            ]);
        }
    }
