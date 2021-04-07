<?php
declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\PuzzleTaskDto;
use PHPUnit\Framework\TestCase;

class PuzzleTaskDtoTest extends TestCase
{
    private const CHAT_ID = 1;
    private const USER_ID = 2;
    private const ANSWER = 'answer';
    private const TASK_MESSAGE_ID = 3;
    private const ATTEMPT = 4;

    /**
     * @covers \App\Dto\PuzzleTaskDto::getTaskMessageId
     * @covers \App\Dto\PuzzleTaskDto::__construct
     */
    public function testGetTaskMessageId(): void
    {
        $puzzleTaskDto = new PuzzleTaskDto(
            self::CHAT_ID,
            self::USER_ID,
            self::ANSWER,
            self::TASK_MESSAGE_ID,
            self::ATTEMPT
        );
        $this->assertEquals(self::TASK_MESSAGE_ID, $puzzleTaskDto->getTaskMessageId());
    }

    /**
     * @covers \App\Dto\PuzzleTaskDto::getAnswer
     * @covers \App\Dto\PuzzleTaskDto::__construct
     */
    public function testGetAnswer(): void
    {
        $puzzleTaskDto = new PuzzleTaskDto(
            self::CHAT_ID,
            self::USER_ID,
            self::ANSWER,
            self::TASK_MESSAGE_ID,
            self::ATTEMPT
        );
        $this->assertEquals(self::ANSWER, $puzzleTaskDto->getAnswer());
    }

    /**
     * @covers \App\Dto\PuzzleTaskDto::getAttempt
     * @covers \App\Dto\PuzzleTaskDto::__construct
     */
    public function testGetAttempt(): void
    {
        $puzzleTaskDto = new PuzzleTaskDto(
            self::CHAT_ID,
            self::USER_ID,
            self::ANSWER,
            self::TASK_MESSAGE_ID,
            self::ATTEMPT
        );
        $this->assertEquals(self::ATTEMPT, $puzzleTaskDto->getAttempt());
    }

    /**
     * @covers \App\Dto\PuzzleTaskDto::getChatId
     * @covers \App\Dto\PuzzleTaskDto::__construct
     */
    public function testGetChatId(): void
    {
        $puzzleTaskDto = new PuzzleTaskDto(
            self::CHAT_ID,
            self::USER_ID,
            self::ANSWER,
            self::TASK_MESSAGE_ID,
            self::ATTEMPT
        );
        $this->assertEquals(self::CHAT_ID, $puzzleTaskDto->getChatId());
    }

    /**
     * @covers \App\Dto\PuzzleTaskDto::getUserId
     * @covers \App\Dto\PuzzleTaskDto::__construct
     */
    public function testGetUserId(): void
    {
        $puzzleTaskDto = new PuzzleTaskDto(
            self::CHAT_ID,
            self::USER_ID,
            self::ANSWER,
            self::TASK_MESSAGE_ID,
            self::ATTEMPT
        );
        $this->assertEquals(self::USER_ID, $puzzleTaskDto->getUserId());
    }
}
