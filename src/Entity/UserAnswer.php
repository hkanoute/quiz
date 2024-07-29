<?php

// src/Entity/UserAnswer.php

namespace App\Entity;

use App\Repository\UserAnswerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAnswerRepository::class)]
class UserAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: QuizAttempt::class, inversedBy: "answers")]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizAttempt $quizAttempt = null;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(type: "json")]
    private array $answer = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuizAttempt(): QuizAttempt
    {
        return $this->quizAttempt;
    }

    public function setQuizAttempt(QuizAttempt $quizAttempt): self
    {
        $this->quizAttempt = $quizAttempt;

        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): array
    {
        return $this->answer;
    }

    public function setAnswer(mixed $answer): self
    {
        $this->answer = $answer;

        return $this;
    }
}
