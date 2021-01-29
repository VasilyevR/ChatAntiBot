<?php
declare(strict_types=1);

namespace App\Puzzle;

use Noodlehaus\Config;
use Noodlehaus\Parser\Php;

class RiddlePuzzleGenerator extends AbstractPuzzleGenerator
{
    protected const MAX_CHOICES_COUNT = 4;

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

        return array_splice($riddlesAnswers, 0, self::MAX_CHOICES_COUNT);
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
        $config = new Config('config/riddles.php', new Php());

        return $config->all();
    }

    protected function generateOneAnswer()
    {
    }
}
