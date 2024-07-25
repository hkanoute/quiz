<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\FalseAnswer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuizType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QuizController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $em, )
    {
    }

    #[Route('/quiz', name: 'app_quiz')]
    public function index(Request $request): Response
    {
        $quiz = new Quiz();

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($quiz);
            $this->em->flush();

            return $this->redirectToRoute('app_quiz');
        }

        return $this->render('quiz/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/quiz/{id}', name: 'app_quiz_show')]
    public function show(Quiz $quiz): Response
    {
        return $this->render('quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/quiz/{id}/edit', name: 'app_quiz_edit')]
    public function edit(Quiz $quiz, Request $request): Response
    {
        $originalQuestions = new ArrayCollection();
        $originalAnswers = new ArrayCollection();
        $originalFalseAnswers = new ArrayCollection();

        foreach ($quiz->getQuestions() as $question) {
            $originalQuestions->add($question);
            foreach ($question->getAnswers() as $answer) {
                $originalAnswers->add($answer);
            }
            foreach ($question->getFalseAnswers() as $falseAnswer) {
                $originalFalseAnswers->add($falseAnswer);
            }
        }

        $editForm = $this->createForm(QuizType::class, $quiz);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            foreach ($originalQuestions as $question) {
                if (false === $quiz->getQuestions()->contains($question)) {
                    $quiz->removeQuestion($question);
                    $this->em->remove($question);
                }
                foreach ($originalAnswers as $answer) {
                    if (false === $question->getAnswers()->contains($answer)) {
                        $question->removeAnswer($answer);
                        $this->em->remove($question);
                    }
                }
                foreach ($originalFalseAnswers as $falseAnswer) {
                    if (false === $question->getFalseAnswers()->contains($falseAnswer)) {
                        $question->removeFalseAnswer($falseAnswer);
                        $this->em->remove($question);
                    }
                }
            }
            $this->em->persist($quiz);
            $this->em->flush();

            return $this->redirectToRoute('app_quiz_show', ['id' => $quiz->getId()]);
        }

        return $this->render('quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $editForm->createView(),
        ]);
    }



}
