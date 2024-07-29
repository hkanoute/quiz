<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Quiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content',TextareaType::class, [
                'label' => 'Question',
                'attr' => ['class' => 'form-control mb-3'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'short' => 'short',
                    'qcm' => 'qcm',
                    'multiple' => 'multiple',
                ],
                'expanded' => false,
                'attr' => ['class' => 'form-control mb-3'],

            ])
            ->add('answers', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_name' => '__answers_name__',
                'attr' => [
                    'data-prototype-name' => '__answers_name__',
                    'data-entry-add-label' => 'Add Answer',
                ]
            ])
            ->add('falseAnswers', CollectionType::class, [
                'entry_type' => FalseAnswerType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_name' => '__false_answers_name__',
                'attr' => [
                    'data-prototype-name' => '__false_answers_name__',
                    'data-entry-add-label' => 'Add a false Answer',
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
