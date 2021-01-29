<?php
declare(strict_types=1);

namespace App\Puzzle;

use App\Dto\PuzzlesSettingsDto;
use App\Enum\PuzzleTypeEnum;

class PuzzleFactory
{
    /**
     * @param PuzzlesSettingsDto $puzzlesSettingsDto
     * @return PuzzleGeneratorInterface
     */
    public static function getPuzzleGenerator(PuzzlesSettingsDto $puzzlesSettingsDto): PuzzleGeneratorInterface
    {
        $puzzleType = $puzzlesSettingsDto->getPuzzleType();
        switch ($puzzleType) {
            case PuzzleTypeEnum::RANDOM_NUMBERS:
                return new RandomNumbersPuzzleGenerator($puzzlesSettingsDto->getPuzzleSettings());
            case PuzzleTypeEnum::SIMPLE_MATH:
                return new SimpleMathPuzzleGenerator($puzzlesSettingsDto->getPuzzleSettings());
            case PuzzleTypeEnum::MATH:
                return new MathPuzzleGenerator($puzzlesSettingsDto->getPuzzleSettings());
            case PuzzleTypeEnum::RIDDLES:
                return new RiddlePuzzleGenerator($puzzlesSettingsDto->getPuzzleSettings());
            case PuzzleTypeEnum::FIRST_NUMBERS:
            default:
                return new FirstNumbersPuzzleGenerator($puzzlesSettingsDto->getPuzzleSettings());
        }
    }
}
