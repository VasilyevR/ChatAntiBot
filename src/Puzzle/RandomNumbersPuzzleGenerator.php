<?php
declare(strict_types=1);

namespace App\Puzzle;

class RandomNumbersPuzzleGenerator extends AbstractPuzzleGenerator
{
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
     * @return int
     */
    protected function generateOneAnswer(): int
    {
        return random_int(1, 9);
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
}
