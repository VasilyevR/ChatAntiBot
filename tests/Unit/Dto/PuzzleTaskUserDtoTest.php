<?php
declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\PuzzleTaskUserDto;
use PHPUnit\Framework\TestCase;

class PuzzleTaskUserDtoTest extends TestCase
{
    private const CHAT_ID = 1;
    private const USER_ID = 2;
    private const ENTER_MESSAGE_ID = 2;
    private const PUZZLE_MESSAGE_ID = 3;

    /**
     * @covers \App\Dto\PuzzleTaskUserDto::getUserId
     * @covers \App\Dto\PuzzleTaskUserDto::__construct
     */
    public function testGetUserId(): void
    {
        $puzzleTaskUserDto = new PuzzleTaskUserDto(self::CHAT_ID, self::USER_ID, self::ENTER_MESSAGE_ID, self::PUZZLE_MESSAGE_ID);
        self::assertEquals(self::USER_ID, $puzzleTaskUserDto->getUserId());
    }

    /**
     * @covers \App\Dto\PuzzleTaskUserDto::getChatId
     * @covers \App\Dto\PuzzleTaskUserDto::__construct
     */
    public function testGetChatId(): void
    {
        $puzzleTaskUserDto = new PuzzleTaskUserDto(self::CHAT_ID, self::USER_ID, self::ENTER_MESSAGE_ID, self::PUZZLE_MESSAGE_ID);
        self::assertEquals(self::CHAT_ID, $puzzleTaskUserDto->getChatId());
    }

    /**
     * @covers \App\Dto\PuzzleTaskUserDto::getEnterMessageId
     * @covers \App\Dto\PuzzleTaskUserDto::__construct
     */
    public function testGetEnterMessageId(): void
    {
        $puzzleTaskUserDto = new PuzzleTaskUserDto(self::CHAT_ID, self::USER_ID, self::ENTER_MESSAGE_ID, self::PUZZLE_MESSAGE_ID);
        self::assertEquals(self::ENTER_MESSAGE_ID, $puzzleTaskUserDto->getEnterMessageId());
    }

    /**
     * @covers \App\Dto\PuzzleTaskUserDto::getPuzzleMessageId
     * @covers \App\Dto\PuzzleTaskUserDto::__construct
     */
    public function testGetPuzzleMessageId(): void
    {
        $puzzleTaskUserDto = new PuzzleTaskUserDto(self::CHAT_ID, self::USER_ID, self::ENTER_MESSAGE_ID, self::PUZZLE_MESSAGE_ID);
        self::assertEquals(self::PUZZLE_MESSAGE_ID, $puzzleTaskUserDto->getPuzzleMessageId());
    }
}
