<?php

namespace Tests\Unit\Dto;

use App\Dto\NewMemberTelegramUpdateDto;
use App\Dto\UserDto;
use App\Enum\TelegramUpdateEnum;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class NewMemberTelegramUpdateDtoTest extends TestCase
{
    private const CHAT_ID = 1;
    private const UPDATE_ID = 2;
    private const USER_ID = 3;
    private const MESSAGE_ID = 4;

    /**
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getNewMembers
     * @covers \App\Dto\NewMemberTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetNewMembers(): void
    {
        $newMemberDto = $this->getUserDtoMock();
        $newMemberTelegramUpdateDto = new NewMemberTelegramUpdateDto(
            self::UPDATE_ID,
            new DateTimeImmutable(),
            self::CHAT_ID,
            self::MESSAGE_ID,
            [$newMemberDto]
        );
        self::assertContains($newMemberDto, $newMemberTelegramUpdateDto->getNewMembers());
    }

    /**
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getUpdateId
     * @covers \App\Dto\NewMemberTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetUpdateId(): void
    {
        $newMemberDto = $this->getUserDtoMock();
        $newMemberTelegramUpdateDto = new NewMemberTelegramUpdateDto(
            self::UPDATE_ID,
            new DateTimeImmutable(),
            self::CHAT_ID,
            self::MESSAGE_ID,
            [$newMemberDto]
        );
        self::assertEquals(self::UPDATE_ID, $newMemberTelegramUpdateDto->getUpdateId());
    }

    /**
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getChatId
     * @covers \App\Dto\NewMemberTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetChatId(): void
    {
        $newMemberDto = $this->getUserDtoMock();
        $newMemberTelegramUpdateDto = new NewMemberTelegramUpdateDto(
            self::UPDATE_ID,
            new DateTimeImmutable(),
            self::CHAT_ID,
            self::MESSAGE_ID,
            [$newMemberDto]
        );
        self::assertEquals(self::CHAT_ID, $newMemberTelegramUpdateDto->getChatId());
    }

    /**
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getType
     * @covers \App\Dto\NewMemberTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetType(): void
    {
        $newMemberDto = $this->getUserDtoMock();
        $newMemberTelegramUpdateDto = new NewMemberTelegramUpdateDto(
            self::UPDATE_ID,
            new DateTimeImmutable(),
            self::CHAT_ID,
            self::MESSAGE_ID,
            [$newMemberDto]
        );
        self::assertEquals(TelegramUpdateEnum::NEW_MEMBER, $newMemberTelegramUpdateDto->getType());
    }

    /**
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getMessageId
     * @covers \App\Dto\NewMemberTelegramUpdateDto::__construct
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetMessageId(): void
    {
        $newMemberDto = $this->getUserDtoMock();
        $newMemberTelegramUpdateDto = new NewMemberTelegramUpdateDto(
            self::UPDATE_ID,
            new DateTimeImmutable(),
            self::CHAT_ID,
            self::MESSAGE_ID,
            [$newMemberDto]
        );
        self::assertEquals(self::MESSAGE_ID, $newMemberTelegramUpdateDto->getMessageId());
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
