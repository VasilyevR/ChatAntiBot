<?php

namespace Tests\Unit\Dto;

use App\Dto\BotSettingsDto;
use App\Dto\PuzzleSettingsDto;
use App\Dto\PuzzlesSettingsDto;
use PHPUnit\Framework\TestCase;

class BotSettingsDtoTest extends TestCase
{
    private const BOT_API_KEY = 'BOT_API_KEY';
    private const PUZZLE_REPLY_TIME_OUT = 'PUZZLE_REPLY_TIME_OUT';
    private const PUZZLE_REPLY_ATTEMPT_COUNT = 'PUZZLE_REPLY_ATTEMPT_COUNT';
    private const USER_NAME = 'USER_NAME';
    private const WELCOME_MESSAGE = 'WELCOME_MESSAGE';
    private const INTRO_MESSAGE = 'INTRO_MESSAGE';

    /**
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::getBotApiKey
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testSetGetBotApiKey(array $goodConfig): void
    {
        $botApiKeyValue = $goodConfig[self::BOT_API_KEY];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setBotApiKey($botApiKeyValue);
        self::assertEquals($botApiKeyValue, $botSettingsDto->getBotApiKey());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testSetGetPuzzleReplyTimeOut(array $goodConfig): void
    {
        $botApiKeyValue = $goodConfig[self::PUZZLE_REPLY_TIME_OUT];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setPuzzleReplyTimeOut($botApiKeyValue);
        self::assertEquals($botApiKeyValue, $botSettingsDto->getPuzzleReplyTimeOut());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::getBotUserName
     */
    public function testSetGetBotUserName(): void
    {
        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setBotUserName(self::USER_NAME);
        self::assertEquals(self::USER_NAME, $botSettingsDto->getBotUserName());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyAttemptCount
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testSetGetPuzzleReplyAttemptCount(array $goodConfig): void
    {
        $puzzleReplyAttemptCount = $goodConfig[self::PUZZLE_REPLY_ATTEMPT_COUNT];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setPuzzleReplyAttemptCount($puzzleReplyAttemptCount);
        self::assertEquals($puzzleReplyAttemptCount, $botSettingsDto->getPuzzleReplyAttemptCount());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setWelcomeMessage
     * @covers \App\Dto\BotSettingsDto::getWelcomeMessage
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testSetGetWelcomeMessage(array $goodConfig): void
    {
        $welcomeMessage = $goodConfig[self::WELCOME_MESSAGE];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setWelcomeMessage($welcomeMessage);
        $this->assertEquals($welcomeMessage, $botSettingsDto->getWelcomeMessage());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setIntroMessage
     * @covers \App\Dto\BotSettingsDto::getIntroMessage
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testSetGetIntroMessage(array $goodConfig): void
    {
        $introMessage = $goodConfig[self::INTRO_MESSAGE];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setIntroMessage($introMessage);
        $this->assertEquals($introMessage, $botSettingsDto->getIntroMessage());
    }

    /**
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::getPuzzlesSettings
     */
    public function testSetGetPuzzlesSettings(): void
    {
        $puzzlesSettings = $this->getMockPuzzlesSettingsDto();

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setPuzzlesSettings($puzzlesSettings);
        self::assertEquals($puzzlesSettings, $botSettingsDto->getPuzzlesSettings());
    }

    /**
     * @return PuzzlesSettingsDto
     */
    protected function getMockPuzzlesSettingsDto(): PuzzlesSettingsDto
    {
        $puzzleSettingsMock = $this->getMockPuzzleSettingsDto();

        return $this
            ->getMockBuilder(PuzzlesSettingsDto::class)
            ->setConstructorArgs(
                [
                    'puzzle_type',
                    $puzzleSettingsMock
                ]
            )
            ->getMock();
    }

    /**
     * @return PuzzleSettingsDto
     */
    protected function getMockPuzzleSettingsDto(): PuzzleSettingsDto
    {
        return $this
            ->getMockBuilder(PuzzleSettingsDto::class)
            ->setConstructorArgs(
                [
                    1,
                    []
                ]
            )
            ->getMock();
    }
}
