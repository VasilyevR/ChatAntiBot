<?php
declare(strict_types=1);

namespace Tests\Functional;

use App\BotSettingsLoader;
use App\DatabaseService;
use App\TelegramBotClient;
use App\UpdateManager;
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
use TgBotApi\BotApiBase\Type\ChatType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;

class UnnecessaryUpdateTest extends TestCase
{
    private const CHAT_ID = 1;
    private const MESSAGE_ID = 3;

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
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\UnnecessaryTelegramUpdateDto::__construct
     * @covers \App\Dto\UnnecessaryTelegramUpdateDto::getType
     * @covers \App\Dto\UnnecessaryTelegramUpdateDto::getUpdateId
     * @covers \App\PuzzleTask::__construct
     * @covers \App\PuzzleTask::getNonApprovedUsers
     * @covers \App\TelegramBotClient::__construct
     * @covers \App\TelegramBotClient::getUpdates
     * @covers \App\TelegramBotClient::getUserName
     * @covers \App\TelegramSettings::__construct
     * @covers \App\TelegramSettings::getMessageOffset
     * @covers \App\TelegramSettings::setMessageOffset
     * @covers \App\UpdateManager::run
     * @covers \App\UpdateProcessor\NewMembersProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::banNonApprovedMembers
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::__construct
     * @covers \App\UpdateProcessor\UnnecessaryProcessor::__construct
     * @covers \App\UpdateProcessor\UnnecessaryProcessor::processUpdate
     * @covers \App\UpdateProcessor\UpdateProcessorManager::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::getUpdateProcessorByUpdateType
     * @covers \App\UpdatesProvider::getUnnecessaryUpdateDto
     * @covers \App\UpdatesProvider::getUpdatesDtos
     * @covers \App\UpdatesProvider::isCorrectNewMemberUpdate
     * @covers \App\UpdatesProvider::isCorrectPuzzleAnswerUpdate
     */
    public function testUnnecessaryUpdate(): void
    {
        $goodConfig = GoodConfigProvider::getGoodConfig();
        $goodConfigJson = json_encode($goodConfig[0][0]);

        $logger = $this->getMockLogger();
        $botApi = $this->getMockBotApi();

        $botUserType = $this->getMockBotUserType();
        $botApi->expects(self::once())
            ->method('getMe')
            ->willReturn($botUserType);

        $botApi->expects(self::never())
            ->method('send');

        $botApi->expects(self::never())
            ->method('delete');

        $botApi->expects(self::never())
            ->method('kick');

        $botApi->expects(self::never())
            ->method('restrict');

        $updateType = $this->getUnnecessaryUpdateType();
        $botApi->expects(self::once())
            ->method('getUpdates')
            ->willReturn([$updateType]);

        $database = new SQLite3(':memory:', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

        $config = new Config($goodConfigJson, new Json(), true);
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsDto = $botSettingsLoader->getBotSettings();
        $botClient = new TelegramBotClient($botApi);
        DatabaseService::init($database);
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
     * @return UpdateType
     */
    private function getUnnecessaryUpdateType(): UpdateType
    {
        $chat = new ChatType();
        $chat->id = self::CHAT_ID;

        $message = new MessageType();
        $message->messageId = self::MESSAGE_ID;
        $message->chat = $chat;

        $updateType = new UpdateType();
        $updateType->updateId = 1;
        $updateType->message = $message;

        return $updateType;
    }
}
