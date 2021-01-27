<?php
declare(strict_types=1);

namespace App\Puzzle;

class MathPuzzleGenerator extends AbstractPuzzleGenerator
{
    protected function generateQuestion($chosenAnswer): string
    {
        $firstActionNumber = $this->getRandomActionNumber();
        $secondActionNumber = $this->getRandomActionNumber();
        $beginActionNumber = $chosenAnswer - $firstActionNumber - $secondActionNumber;

        $beginNumber = $this->getFormattedNumber($beginActionNumber);
        $firstNumber = $this->getFormattedNumber($firstActionNumber);
        $secondNumber = $this->getFormattedNumber($secondActionNumber);

        return sprintf(
            'Сколько будет, если к %s прибавить %s, а затем прибавить %s?',
            $beginNumber,
            $firstNumber,
            $secondNumber
        );
    }

    /**
     * @return int
     */
    protected function generateOneAnswer()
    {
        return random_int(101, 999);
    }

    /**
     * @return int
     */
    protected function getRandomActionNumber(): int
    {
        return random_int(10, 50);
    }

    /**
     * @param array $answers
     * @return int
     */
    protected function getChosenAnswer(array $answers): int
    {
        return $this->getRandomAnswer($answers);
    }

    protected function getChoiceByAnswer($answer): string
    {
        return (string)$answer;
    }
}
