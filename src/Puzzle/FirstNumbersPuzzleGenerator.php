<?php
declare(strict_types=1);

namespace App\Puzzle;

class FirstNumbersPuzzleGenerator extends AbstractPuzzleGenerator
{
    protected function getAnswers(): array
    {
        $maxChoicesCount = $this->getMaxChoicesCount();
        $answers = [];
        for ($i = 1; $i <= $maxChoicesCount; $i++) {
            $answers[] = $i;
        }
        return $answers;
    }

    /**
     * @param $chosenAnswer
     * @return string
     */
    protected function generateQuestion($chosenAnswer): string
    {
        $answer = $this->getFormattedNumber($chosenAnswer);

        return sprintf('Выбери число %s', $answer);
    }

    /**
     * @param int[] $answers
     * @return int
     */
    protected function getChosenAnswer(array $answers): int
    {
        return $this->getRandomAnswer($answers);
    }

    /**
     * @param $answer
     * @return string
     */
    protected function getChoiceByAnswer($answer): string
    {
        return (string)$answer;
    }

    protected function generateOneAnswer()
    {
    }
}
