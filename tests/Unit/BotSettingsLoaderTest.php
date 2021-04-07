<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\BotSettingsLoader;
use App\Dto\BotSettingsDto;
use Monolog\Logger;
use Noodlehaus\Parser\Php;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Noodlehaus\Config;
use Psr\Log\LoggerInterface;
use RuntimeException;

class BotSettingsLoaderTest extends TestCase
{
    private const BOT_API_KEY = 'BOT_API_KEY';
    private const PUZZLE_REPLY_TIME_OUT = 'PUZZLE_REPLY_TIME_OUT';
    private const PUZZLE_REPLY_ATTEMPT_COUNT = 'PUZZLE_REPLY_ATTEMPT_COUNT';
    private const PUZZLE_SETTINGS = 'PUZZLE_SETTINGS';

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testGetBotSettings(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects($this->exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, $goodConfig[self::PUZZLE_REPLY_TIME_OUT]],
                    [self::PUZZLE_REPLY_ATTEMPT_COUNT, null, $goodConfig[self::PUZZLE_REPLY_ATTEMPT_COUNT]],
                    [self::PUZZLE_SETTINGS, null, $goodConfig[self::PUZZLE_SETTINGS]]
                ]
            );

        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsDto = $botSettingsLoader->getBotSettings();
        $this->assertInstanceOf(BotSettingsDto::class, $botSettingsDto);
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     */
    public function testGetBotSettingsWithoutApiKey(): void
    {
        $config = $this->getMockConfig();

        $config->expects($this->once())
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, null],
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testGetBotSettingsWithoutReplyTimeOut(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, null],
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testGetBotSettingsWithoutReplyTimeCount(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, $goodConfig[self::PUZZLE_REPLY_TIME_OUT]],
                    [self::PUZZLE_REPLY_ATTEMPT_COUNT, null, null],
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testGetBotSettingsWithoutPuzzleSettings(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects($this->exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, $goodConfig[self::PUZZLE_REPLY_TIME_OUT]],
                    [self::PUZZLE_REPLY_ATTEMPT_COUNT, null, $goodConfig[self::PUZZLE_REPLY_ATTEMPT_COUNT]],
                    [self::PUZZLE_SETTINGS, null, null]
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @return Config|MockObject
     */
    protected function getMockConfig(): Config
    {
        return $this->getMockBuilder(Config::class)
                        ->setConstructorArgs(['config/parameters.php', new Php()])
                        ->setMethods(['get'])
                        ->getMock();
    }

    /**
     * @return LoggerInterface
     */
    protected function getMockLogger(): LoggerInterface
    {
        return $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['bot'])
            ->setMethods(['critical'])
            ->getMock();
    }
}
