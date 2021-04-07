<?php

namespace Tests\Unit\Puzzle;

use App\Dto\PuzzleSettingsDto;
use App\Dto\PuzzlesSettingsDto;
use App\Enum\PuzzleTypeEnum;
use App\Puzzle\FirstNumbersPuzzleGenerator;
use App\Puzzle\MathPuzzleGenerator;
use App\Puzzle\PuzzleFactory;
use App\Puzzle\RandomNumbersPuzzleGenerator;
use App\Puzzle\RiddlePuzzleGenerator;
use App\Puzzle\SimpleMathPuzzleGenerator;
use PHPUnit\Framework\TestCase;

class PuzzleFactoryTest extends TestCase
{
    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testGetFirstNumbersPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::FIRST_NUMBERS,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(FirstNumbersPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testGetMathPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::MATH,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(MathPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testRandomNumbersPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::RANDOM_NUMBERS,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(RandomNumbersPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testSimpleMathPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::SIMPLE_MATH,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(SimpleMathPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testRiddlePuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::RIDDLES,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(RiddlePuzzleGenerator::class, $puzzleGenerator);
    }
}
