<?php
declare(strict_types=1);

namespace App\Puzzle;

class SimpleMathPuzzleGenerator extends MathPuzzleGenerator
{
    protected function generateOneAnswer()
    {
        return random_int(21, 30);
    }

    /**
     * @return int
     */
    protected function getRandomActionNumber(): int
    {
        return random_int(1, 10);
    }
}
