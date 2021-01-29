<?php
declare(strict_types=1);

namespace App\Puzzle;

class RiddlePuzzleGenerator extends AbstractPuzzleGenerator
{
    /**
     * @param $chosenAnswer
     * @return string
     */
    protected function generateQuestion($chosenAnswer): string
    {
        $riddles = $this->getRiddles();

        return sprintf("Угадай, кто или что это: %s", $riddles[$chosenAnswer]);
    }

    protected function getAnswers(): array
    {
        $riddles = $this->getRiddles();
        $riddlesAnswers = array_keys($riddles);
        shuffle($riddlesAnswers);

        return array_splice($riddlesAnswers, 0, $this->getMaxChoicesCount());
    }

    /**
     * @param array $answers
     * @return string
     */
    protected function getChosenAnswer(array $answers): string
    {
        return $this->getRandomAnswer($answers);
    }

    protected function getChoiceByAnswer($answer): string
    {
        return $answer;
    }

    /**
     * @return string[]
     */
    private function getRiddles(): array
    {
        return $this->settings;
    }

    protected function generateOneAnswer()
    {
    }
}
