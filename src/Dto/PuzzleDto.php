<?php
declare(strict_types=1);

namespace App\Dto;

class PuzzleDto
{
    /**
     * @var string
     */
    private $question;

    /**
     * @var string
     */
    private $answer;

    /**
     * @var string[]
     */
    private $choices;

    /**
     * @param string $question
     * @param string $answer
     * @param string[] $choices
     */
    public function __construct(string $question, string $answer, array $choices)
    {
        $this->question = $question;
        $this->answer = $answer;
        $this->choices = $choices;
    }

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }

    /**
     * @return string[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }
}
