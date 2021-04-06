<?php

namespace unit\Dto;

use App\Dto\PuzzleAnswerTelegramUpdateDto;
use App\Dto\UserDto;
use App\Enum\TelegramUpdateEnum;
use PHPUnit\Framework\TestCase;

class PuzzleAnswerTelegramUpdateDtoTest extends TestCase
{
    private const USER_ID = 1;
    private const UPDATE_ID = 2;
    private const CHAT_ID = 3;
    private const ENTER_MESSAGE_ID = 4;
    private const PUZZLE_MESSAGE_ID = 5;
    private const ANSWER = 'ANSWER';

    /**
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getChatId()
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetChatId(): void
    {
        $userDto = $this->getUserDtoMock();
        $puzzleAnswerTelegramUpdateDto = new PuzzleAnswerTelegramUpdateDto(
            self::UPDATE_ID,
            self::CHAT_ID,
            $userDto,
            self::ENTER_MESSAGE_ID,
            self::PUZZLE_MESSAGE_ID,
            self::ANSWER
        );
        $this->assertEquals(self::CHAT_ID, $puzzleAnswerTelegramUpdateDto->getChatId());
    }

    /**
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUpdateId()
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetUpdateId(): void
    {
        $userDto = $this->getUserDtoMock();
        $puzzleAnswerTelegramUpdateDto = new PuzzleAnswerTelegramUpdateDto(
            self::UPDATE_ID,
            self::CHAT_ID,
            $userDto,
            self::ENTER_MESSAGE_ID,
            self::PUZZLE_MESSAGE_ID,
            self::ANSWER
        );
        $this->assertEquals(self::UPDATE_ID, $puzzleAnswerTelegramUpdateDto->getUpdateId());
    }

    /**
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getEnterMessageId()
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetEnterMessageId(): void
    {
        $userDto = $this->getUserDtoMock();
        $puzzleAnswerTelegramUpdateDto = new PuzzleAnswerTelegramUpdateDto(
            self::UPDATE_ID,
            self::CHAT_ID,
            $userDto,
            self::ENTER_MESSAGE_ID,
            self::PUZZLE_MESSAGE_ID,
            self::ANSWER
        );
        $this->assertEquals(self::ENTER_MESSAGE_ID, $puzzleAnswerTelegramUpdateDto->getEnterMessageId());
    }

    /**
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUser()
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetUser(): void
    {
        $userDto = $this->getUserDtoMock();
        $puzzleAnswerTelegramUpdateDto = new PuzzleAnswerTelegramUpdateDto(
            self::UPDATE_ID,
            self::CHAT_ID,
            $userDto,
            self::ENTER_MESSAGE_ID,
            self::PUZZLE_MESSAGE_ID,
            self::ANSWER
        );
        $this->assertEquals($userDto, $puzzleAnswerTelegramUpdateDto->getUser());
    }

    /**
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getType()
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetType(): void
    {
        $userDto = $this->getUserDtoMock();
        $puzzleAnswerTelegramUpdateDto = new PuzzleAnswerTelegramUpdateDto(
            self::UPDATE_ID,
            self::CHAT_ID,
            $userDto,
            self::ENTER_MESSAGE_ID,
            self::PUZZLE_MESSAGE_ID,
            self::ANSWER
        );
        $this->assertEquals(TelegramUpdateEnum::PUZZLE_ANSWER, $puzzleAnswerTelegramUpdateDto->getType());
    }

    /**
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getAnswer()
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetAnswer(): void
    {
        $userDto = $this->getUserDtoMock();
        $puzzleAnswerTelegramUpdateDto = new PuzzleAnswerTelegramUpdateDto(
            self::UPDATE_ID,
            self::CHAT_ID,
            $userDto,
            self::ENTER_MESSAGE_ID,
            self::PUZZLE_MESSAGE_ID,
            self::ANSWER
        );
        $this->assertEquals(self::ANSWER, $puzzleAnswerTelegramUpdateDto->getAnswer());
    }

    /**
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getPuzzleMessageId()
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetPuzzleMessageId(): void
    {
        $userDto = $this->getUserDtoMock();
        $puzzleAnswerTelegramUpdateDto = new PuzzleAnswerTelegramUpdateDto(
            self::UPDATE_ID,
            self::CHAT_ID,
            $userDto,
            self::ENTER_MESSAGE_ID,
            self::PUZZLE_MESSAGE_ID,
            self::ANSWER
        );
        $this->assertEquals(self::PUZZLE_MESSAGE_ID, $puzzleAnswerTelegramUpdateDto->getPuzzleMessageId());
    }

    /**
     * @return UserDto
     */
    private function getUserDtoMock(): UserDto
    {
        return $this
            ->getMockBuilder(UserDto::class)
            ->setConstructorArgs([self::USER_ID, 'name'])
            ->getMock();
    }
}
