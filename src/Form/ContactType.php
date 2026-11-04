<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'constraints' => [new NotBlank(), new Length(max: 120)],
                'attr' => ['autocomplete' => 'name', 'placeholder' => 'Your name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank(), new Email(), new Length(max: 180)],
                'attr' => ['autocomplete' => 'email', 'placeholder' => 'you@example.com'],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'constraints' => [new NotBlank(), new Length(max: 180)],
                'attr' => ['placeholder' => 'What can we help you with?'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'constraints' => [new NotBlank(), new Length(min: 10, max: 5000)],
                'attr' => ['rows' => 7, 'placeholder' => 'Tell us a little about your project…'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => true]);
    }
}
