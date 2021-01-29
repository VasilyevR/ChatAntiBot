<?php
declare(strict_types=1);

namespace App\Puzzle;

use App\Dto\PuzzleDto;
use App\Dto\PuzzleSettingsDto;
use InvalidArgumentException;
use NumberFormatter;

abstract class AbstractPuzzleGenerator implements PuzzleGeneratorInterface
{
    /**
     * @var int
     */
    protected $maxChoicesCount;

    /**
     * @var string[]
     */
    protected $settings;

    /**
     * @param PuzzleSettingsDto $puzzleSettingsDto
     */
    public function __construct(PuzzleSettingsDto $puzzleSettingsDto)
    {
        $this->maxChoicesCount = $puzzleSettingsDto->getMaxChoicesCount();
        $this->settings = $puzzleSettingsDto->getSettings();
    }

    /**
     * @return PuzzleDto
     */
    public function generate(): PuzzleDto
    {
        $answers = $this->getAnswers();
        $chosenAnswer = $this->getChosenAnswer($answers);
        $choices = $this->generateChoices($answers);
        $question = $this->generateQuestion($chosenAnswer);

        return new PuzzleDto($question, (string)$chosenAnswer, $choices);
    }

    abstract protected function generateQuestion($chosenAnswer): string;

    abstract protected function generateOneAnswer();

    abstract protected function getChosenAnswer(array $answers);

    abstract protected function getChoiceByAnswer($answer): string;

    protected function getAnswers(): array
    {
        $maxChoicesCount = $this->getMaxChoicesCount();
        $answers = [];
        for ($i = 1; $i <= $maxChoicesCount; $i++) {
            $answers[] = $this->generateOneAnswer();
        }
        return $answers;
    }

    /**
     * @param array $answers
     * @return array
     */
    protected function generateChoices(array $answers): array
    {
        return array_map(
            function ($answer) {
                return $this->getChoiceByAnswer($answer);
            },
            $answers
        );
    }

    /**
     * @return int
     */
    protected function getMaxChoicesCount(): int
    {
        return $this->maxChoicesCount;
    }

    protected function getRandomAnswer(array $answers)
    {
        shuffle($answers);

        return reset($answers);
    }

    /**
     * @param int $number
     * @return string
     */
    protected function getFormattedNumber(int $number): string
    {
        $formatter = new NumberFormatter("ru", NumberFormatter::SPELLOUT);
        $formattedNumber = $formatter->format($number);
        if (false === $formattedNumber) {
            throw new InvalidArgumentException(
                sprintf('Formatter argument exception on %d', $number)
            );
        }

        return $formattedNumber;
    }
}
