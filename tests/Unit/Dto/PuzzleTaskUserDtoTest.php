<?php
declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\PuzzleTaskUserDto;
use PHPUnit\Framework\TestCase;

class PuzzleTaskUserDtoTest extends TestCase
{
    private const CHAT_ID = 1;
    private const USER_ID = 2;

    /**
     * @covers \App\Dto\PuzzleTaskUserDto::getUserId
     * @covers \App\Dto\PuzzleTaskUserDto::__construct
     */
    public function testGetUserId(): void
    {
        $puzzleTaskUserDto = new PuzzleTaskUserDto(self::CHAT_ID, self::USER_ID);
        $this->assertEquals(self::USER_ID, $puzzleTaskUserDto->getUserId());
    }

    /**
     * @covers \App\Dto\PuzzleTaskUserDto::getChatId
     * @covers \App\Dto\PuzzleTaskUserDto::__construct
     */
    public function testGetChatId(): void
    {
        $puzzleTaskUserDto = new PuzzleTaskUserDto(self::CHAT_ID, self::USER_ID);
        $this->assertEquals(self::CHAT_ID, $puzzleTaskUserDto->getChatId());
    }
}
