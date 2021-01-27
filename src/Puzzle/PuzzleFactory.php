<?php
declare(strict_types=1);

namespace App\Puzzle;

use App\Enum\PuzzleTypeEnum;

class PuzzleFactory
{
    /**
     * @param string $puzzleType
     * @return PuzzleGeneratorInterface
     */
    public static function getPuzzleGenerator(string $puzzleType): PuzzleGeneratorInterface
    {
        switch ($puzzleType) {
            case PuzzleTypeEnum::RANDOM_NUMBERS:
                return new RandomNumbersPuzzleGenerator();
            case PuzzleTypeEnum::SIMPLE_MATH:
                return new SimpleMathPuzzleGenerator();
            case PuzzleTypeEnum::MATH:
                return new MathPuzzleGenerator();
            case PuzzleTypeEnum::RIDDLES:
                return new RiddlePuzzleGenerator();
            case PuzzleTypeEnum::FIRST_NUMBERS:
            default:
                return new FirstNumbersPuzzleGenerator();
        }
    }
}
