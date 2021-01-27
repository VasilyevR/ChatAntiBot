<?php
declare(strict_types=1);

namespace App\Puzzle;

use App\Dto\PuzzleDto;

interface PuzzleGeneratorInterface
{
    public function generate(): PuzzleDto;
}
