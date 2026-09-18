<?php

    namespace App\Form;

    use App\Entity\Media;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Validator\Constraints\File;

    class MediaType extends AbstractType
    {
        public function buildForm(
            FormBuilderInterface $builder,
            array $options
        ): void {
            $builder
                ->add('media', FileType::class, [
                    'label' => 'Fichier',
                    'constraints' => [
                        new File([
                            'maxSize' => '100M',
                            'mimeTypes' => [
                                'video/mp4',
                                'video/quicktime',
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                            ],
                            'mimeTypesMessage' => 'Veuillez uploader une vidéo mp4/quicktime ou une image (jpeg, png, gif).',
                        ]),
                    ],
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Enregistrer',
                ]);
        }

        public function configureOptions(
            OptionsResolver $resolver
        ): void {
//            $resolver->setDefaults([
//                'data_class' => Media::class,
//            ]);
        }
    }
