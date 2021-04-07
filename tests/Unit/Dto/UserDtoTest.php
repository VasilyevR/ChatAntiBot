<?php
declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\UserDto;
use PHPUnit\Framework\TestCase;

class UserDtoTest extends TestCase
{
    private const USER_ID = 1;
    private const USER_NAME = 'Name';

    /**
     * @covers \App\Dto\UserDto::getUserId
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetUserId(): void
    {
        $userDto = new UserDto(self::USER_ID, self::USER_NAME);
        self::assertEquals(self::USER_ID, $userDto->getUserId());
    }

    /**
     * @covers \App\Dto\UserDto::getUserName
     * @covers \App\Dto\UserDto::__construct
     */
    public function testGetUserName(): void
    {
        $userDto = new UserDto(self::USER_ID, self::USER_NAME);
        self::assertEquals(self::USER_NAME, $userDto->getUserName());
    }
}
