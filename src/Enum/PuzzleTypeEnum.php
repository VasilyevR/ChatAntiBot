<?php
declare(strict_types=1);

namespace App\Enum;

class PuzzleTypeEnum
{
    public const FIRST_NUMBERS = 'first_numbers';
    public const RANDOM_NUMBERS = 'random_numbers';
    public const SIMPLE_MATH = 'simple_math';
    public const MATH = 'math';
    public const RIDDLES = 'riddles';

    public const ALL = [
        self::FIRST_NUMBERS,
        self::RANDOM_NUMBERS,
        self::SIMPLE_MATH,
        self::MATH,
        self::RIDDLES,
    ];
}
