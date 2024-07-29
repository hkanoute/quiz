<?php

// src/Form/QuizPlayType.php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizPlayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

            foreach ($options['questions'] as $question) {
                $builder->add($question->getId(), QuestionPlayType::class, [
                    'label' => $question->getContent(),
                    'question' => $question,
                    'mapped' => false,
                    'allow_extra_fields' => true,
                ]);
            }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
            'questions' => [],
        ]);
    }
}
