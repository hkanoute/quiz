<?php

namespace App\Form;

use App\Entity\Question;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionPlayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $question = $options['question'];

        $answers = $question->getAnswers()->toArray();
        $falseAnswers = $question->getFalseAnswers()->toArray();
        $choices = array_merge($answers, $falseAnswers);
        $numberOfGoodAnswers = count($answers);

        foreach ($choices as $key => $choice) {
            $choices[$choice->getContent()] = $choice->getContent();
            unset($choices[$key]);
        }

        switch ($question->getType()) {
            case 'short':
                $builder->add('answer', TextType::class, [
                    'label' => 'Réponse',
                    'mapped' => false,
                    'required' => false,
                ]);
                break;
            case 'qcm':
                $builder->add('answers', ChoiceType::class, [
                    'label' => 'Choississez la ou les bonnes réponses',
                    'choices' => $choices,
                    'multiple' => true,
                    'expanded' => true,
                    'mapped' => false,
                ]);
                break;
            case 'multiple':
                for ($i = 0; $i < $numberOfGoodAnswers; $i++) {
                    $builder->add('answers_' . $i, TextType::class, [
                        'label' => 'Réponse numéro ' . ($i + 1),
                        'mapped' => false,
                        'required' => false,
                    ]);
                }
                break;

        }


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
            'question' => [],
        ]);
    }
}
