<?php
declare(strict_types=1);

namespace Tests\Functional;

use App\BotSettingsLoader;
use App\DatabaseService;
use App\TelegramBotClient;
use App\UpdateManager;
use DateInterval;
use DateTimeImmutable;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Monolog\Logger;
use Noodlehaus\Config;
use Noodlehaus\Parser\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SQLite3;
use Tests\DataProviders\GoodConfigProvider;
use TgBotApi\BotApiBase\ApiClient;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\BotApiNormalizer;
use TgBotApi\BotApiBase\Type\UserType;

class SilentMemberTest extends TestCase
{
    private const CHAT_ID = 3;
    private const USER_ID = 5;
    private const GOOD_ANSWER = 'good_answer';
    private const MESSAGE_ENTER_ID = 7;
    private const MESSAGE_PUZZLE_ID = 8;

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\DatabaseService::init
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::setIntroMessage
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setWelcomeMessage
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleTaskUserDto::__construct
     * @covers \App\Dto\PuzzleTaskUserDto::getChatId
     * @covers \App\Dto\PuzzleTaskUserDto::getPuzzleMessageId
     * @covers \App\Dto\PuzzleTaskUserDto::getEnterMessageId
     * @covers \App\Dto\PuzzleTaskUserDto::getUserId
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\PuzzleTask::__construct
     * @covers \App\PuzzleTask::deletePuzzleTask
     * @covers \App\PuzzleTask::getNonApprovedUsers
     * @covers \App\TelegramBotClient::__construct
     * @covers \App\TelegramBotClient::banUser
     * @covers \App\TelegramBotClient::deleteMessage
     * @covers \App\TelegramBotClient::getUpdates
     * @covers \App\TelegramBotClient::getUserName
     * @covers \App\TelegramSettings::__construct
     * @covers \App\TelegramSettings::getMessageOffset
     * @covers \App\UpdateManager::run
     * @covers \App\UpdatesProvider::getUpdatesDtos
     * @covers \App\UpdateProcessor\NewMembersProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::banNonApprovedMembers
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::deletePuzzleMessage
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::deleteEnterMessage
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::__construct
     * @covers \App\UpdateProcessor\UnnecessaryProcessor::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::__construct
     */
    public function testKickSilentMember(): void
    {
        $goodConfig = GoodConfigProvider::getGoodConfig();
        $goodConfigJson = json_encode($goodConfig[0][0]);

        $logger = $this->getMockLogger();
        $botApi = $this->getMockBotApi();

        $botApi->expects(self::once())
            ->method('getUpdates')
            ->willReturn([]);

        $botUserType = $this->getMockBotUserType();
        $botApi->expects(self::once())
            ->method('getMe')
            ->willReturn($botUserType);

        $botApi->expects(self::exactly(2))
            ->method('delete');

        $botApi->expects(self::never())
            ->method('restrict');

        $botApi->expects(self::once())
            ->method('kick');

        $botApi->expects(self::never())
            ->method('send');

        $database = new SQLite3(':memory:', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

        $config = new Config($goodConfigJson, new Json(), true);
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsDto = $botSettingsLoader->getBotSettings();
        $botClient = new TelegramBotClient($botApi);
        DatabaseService::init($database);
        $this->insertPuzzleTask($database);
        $updateManager = new UpdateManager();
        $updateManager->run($botClient, $database, $logger, $botSettingsDto);
    }

    /**
     * @return LoggerInterface|MockObject
     */
    private function getMockLogger(): LoggerInterface
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['bot'])
            ->setMethods(['critical', 'error', 'warning'])
            ->getMock();

        $logger->expects(self::never())
            ->method('critical');

        $logger->expects(self::never())
            ->method('error');

        $logger->expects(self::never())
            ->method('warning');

        return $logger;
    }

    /**
     * @return BotApi|MockObject
     */
    private function getMockBotApi(): BotApi
    {
        $requestFactory = new RequestFactory();
        $streamFactory = new StreamFactory();
        $client = new Client();
        $apiClient = new ApiClient($requestFactory, $streamFactory, $client);

        return $this->getMockBuilder(BotApi::class)
            ->setConstructorArgs(
                [
                    'apikey', $apiClient, new BotApiNormalizer()
                ]
            )
            ->setMethods(['getUpdates', 'send', 'getMe', 'restrict', 'delete', 'kick'])
            ->getMock();
    }

    /**
     * @return UserType
     */
    private function getMockBotUserType(): UserType
    {
        $botUserType = new UserType();
        $botUserType->firstName = 'BotName';
        $botUserType->lastName = 'BotName';
        $botUserType->username = 'BotName';
        $botUserType->id = 10;

        return $botUserType;
    }

    /**
     * @param SQLite3 $database
     */
    private function insertPuzzleTask(SQLite3 $database): void
    {
        $database->query(
            sprintf("INSERT INTO puzzle_task (chat_id, user_id, answer, message_id, puzzle_id, attempt, date_time)
     VALUES (%d, %d, '%s', '%s', '%s', 0, %d)",
                    self::CHAT_ID,
                    self::USER_ID,
                    self::GOOD_ANSWER,
                    self::MESSAGE_ENTER_ID,
                    self::MESSAGE_PUZZLE_ID,
                    (new DateTimeImmutable())->sub(new DateInterval('PT5M'))->getTimestamp()
            )
        );
    }
}
