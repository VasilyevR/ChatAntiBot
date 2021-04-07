<?php
declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\PuzzleDto;
use PHPUnit\Framework\TestCase;

class PuzzleDtoTest extends TestCase
{
    private const QUESTION = 'question';
    private const ANSWER = 'answer';
    private const CHOICES = ['choice1', 'choice2'];

    /**
     * @covers \App\Dto\PuzzleDto::__construct
     * @covers \App\Dto\PuzzleDto::getChoices
     */
    public function testGetChoices(): void
    {
        $puzzleDto = new PuzzleDto(self::QUESTION, self::ANSWER, self::CHOICES);
        $this->assertEquals(self::CHOICES, $puzzleDto->getChoices());
    }

    /**
     * @covers \App\Dto\PuzzleDto::__construct
     * @covers \App\Dto\PuzzleDto::getQuestion
     */
    public function testGetQuestion(): void
    {
        $puzzleDto = new PuzzleDto(self::QUESTION, self::ANSWER, self::CHOICES);
        $this->assertEquals(self::QUESTION, $puzzleDto->getQuestion());
    }

    /**
     * @covers \App\Dto\PuzzleDto::__construct
     * @covers \App\Dto\PuzzleDto::getAnswer
     */
    public function testGetAnswer(): void
    {
        $puzzleDto = new PuzzleDto(self::QUESTION, self::ANSWER, self::CHOICES);
        $this->assertEquals(self::ANSWER, $puzzleDto->getAnswer());
    }
}
