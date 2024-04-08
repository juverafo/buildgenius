<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quiz')]
    private Collection $quiz;

    public function __construct()
    {
        $this->quiz = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection<int, Question>
     */
    public function getQuiz(): Collection
    {
        return $this->quiz;
    }

    public function addQuiz(Question $quiz): static
    {
        if (!$this->quiz->contains($quiz)) {
            $this->quiz->add($quiz);
            $quiz->setQuiz($this);
        }

        return $this;
    }

    public function removeQuiz(Question $quiz): static
    {
        if ($this->quiz->removeElement($quiz)) {
            // set the owning side to null (unless already changed)
            if ($quiz->getQuiz() === $this) {
                $quiz->setQuiz(null);
            }
        }

        return $this;
    }
}
