<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\QuizAttempt;
use App\Entity\User;
use App\Entity\UserAnswer;
use App\Form\QuizPlayType;
use App\Form\QuizType;
use App\Repository\QuizAttemptRepository;
use App\Repository\UserAnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QuizController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $em,)
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

            return $this->redirectToRoute('app_quiz_show', ['id' => $quiz->getId()]);
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

    #[Route("/quiz/{id}/play", name: 'app_quiz_play')]
    public function play(Quiz $quiz, Request $request): Response
    {
        $form = $this->createForm(QuizPlayType::class, $quiz, ['questions' => $quiz->getQuestions()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->em->getRepository(User::class)->find(1);
            $quizAttempt = new QuizAttempt();
            $quizAttempt->setUser($user);
            $quizAttempt->setQuiz($quiz);
            $quizAttempt->setAttemptedAt(new \DateTime());

            $this->em->persist($quizAttempt);

            $data = $form->getData();
            foreach ($data->getQuestions() as $question) {
                $embeddedForm = $form->get($question->getId());
                $data = $embeddedForm->all();
                $nbOfAnswers = count($data);
                $answerArray = [];
                switch ($question->getType()) {
                    case 'short':
                        $answerArray[] = $data['answer']->getData();
                        break;
                    case 'multiple':
                        for ($i = 0; $i < $nbOfAnswers; $i++) {
                            $answerArray[] = $data['answers_' . $i]->getData();
                        }
                        break;
                    case 'qcm':
                        $answerArray = $data['answers']->getData();
                        break;
                }
                $userAnswer = new UserAnswer();
                $userAnswer->setQuizAttempt($quizAttempt);
                $userAnswer->setQuestion($question);
                $userAnswer->setAnswer($answerArray);


                $this->em->persist($userAnswer);
            }

            $this->em->flush();

            return $this->redirectToRoute('quiz_result', ['id' => $quiz->getId()]);
        }

        return $this->render('quiz/play.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }


    #[Route("/quiz/{id}/result", name: 'quiz_result')]
    public function result(Quiz $quiz, QuizAttemptRepository $quizAttemptRepository, UserAnswerRepository $userAnswerRepository): Response
    {
        $user = $this->em->getRepository(User::class)->find(1);
        $quizAttempt = $quizAttemptRepository->findOneBy(['quiz' => $quiz, 'user' => $user], ['attemptedAt' => 'DESC']);
        $userAnswers = $userAnswerRepository->findBy(['quizAttempt' => $quizAttempt]);
        $score = 0;

        foreach ($userAnswers as $userAnswer) {
            $question = $userAnswer->getQuestion();
            $answers = array_map('strtolower', $userAnswer->getAnswer());
            $answers= array_unique($answers);
            $correctAnswers = array_map('strtolower', $question->getAnswers()->map(fn($answer) => $answer->getContent())->toArray());

            if ($question->getType() === 'short') {
                if ($answers[0] === $correctAnswers[0]) {
                    $score += 50;
                }
            } else {
                if (count($answers) === count($correctAnswers) && count(array_diff($answers, $correctAnswers)) === 0) {
                    $score += 50;
                }elseif (count($answers) <= count($correctAnswers)){
                    foreach ($answers as $answer) {
                        if (in_array($answer, $correctAnswers)) {
                            $score += ceil(50 / count($correctAnswers));

                        }
                    }
                }
            }
        }


        $answers = [];
        foreach ($userAnswers as $userAnswer) {
            $question = $userAnswer->getQuestion();
            $answers[] = [
                'question' => $question,
                'userAnswer' => $userAnswer->getAnswer(),
                'correctAnswer' => $question->getAnswers()->map(fn($answer) => $answer->getContent())->toArray(),
            ];
        }

        $quizAttempt->setScore($score);
        $this->em->persist($quizAttempt);
        $this->em->flush();


        return $this->render('quiz/result.html.twig', [
            'quiz' => $quiz,
            'QuizAttemptData' => $answers,
            'quizAttempt' => $quizAttempt,
        ]);
    }

}
