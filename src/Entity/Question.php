<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $answers;

    /**
     * @var Collection<int, FalseAnswer>
     */
    #[ORM\OneToMany(targetEntity: FalseAnswer::class, mappedBy: 'question', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $falseAnswers;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->falseAnswers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FalseAnswer>
     */
    public function getFalseAnswers(): Collection
    {
        return $this->falseAnswers;
    }

    public function addFalseAnswer(FalseAnswer $falseAnswer): static
    {
        if (!$this->falseAnswers->contains($falseAnswer)) {
            $this->falseAnswers->add($falseAnswer);
            $falseAnswer->setQuestion($this);
        }

        return $this;
    }


    public function removeFalseAnswer(FalseAnswer $falseAnswer): static
    {
        if ($this->falseAnswers->removeElement($falseAnswer)) {
            // set the owning side to null (unless already changed)
            if ($falseAnswer->getQuestion() === $this) {
                $falseAnswer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }
}
