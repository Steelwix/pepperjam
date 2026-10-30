<?php

namespace App\Form\Section;

use App\Entity\Activity;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('activity', EntityType::class, [
                'class' => Activity::class,
                'choice_label' => 'title',
                'label' => 'Activité',
                'placeholder' => 'Choisir une activité',
            ])
            ->add('mediaFile', FileType::class, [
                'mapped' => false,
                'multiple' => false,
                'required' => $options['require_media'],
                'label' => 'Médias du projet',
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
                        'mimeTypesMessage' => 'Formats acceptés : MP4, MOV, JPEG, PNG et GIF.',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['require_media'] ? 'Créer le projet' : 'Enregistrer les modifications',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'require_media' => true,
        ]);
        $resolver->setAllowedTypes('require_media', 'bool');
    }
}
