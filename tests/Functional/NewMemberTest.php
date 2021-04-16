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
use TgBotApi\BotApiBase\ApiClient;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\BotApiNormalizer;
use TgBotApi\BotApiBase\Type\ChatType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;
use Tests\DataProviders\GoodConfigProvider;

class NewMemberTest extends TestCase
{
    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\DatabaseService::init
     * @covers \App\Dto\BotSettingsDto::getBotUserName
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::getPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::setIntroMessage
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setWelcomeMessage
     * @covers \App\Dto\NewMemberTelegramUpdateDto::__construct
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getChatId
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getMessageId
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getNewMembers
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getType
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getUpdateId
     * @covers \App\Dto\PuzzleDto::__construct
     * @covers \App\Dto\PuzzleDto::getAnswer
     * @covers \App\Dto\PuzzleDto::getChoices
     * @covers \App\Dto\PuzzleDto::getQuestion
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     * @covers \App\Dto\UserDto::__construct
     * @covers \App\Dto\UserDto::getUserId
     * @covers \App\Dto\UserDto::getUserName
     * @covers \App\PuzzleTask::__construct
     * @covers \App\PuzzleTask::getNonApprovedUsers
     * @covers \App\PuzzleTask::getPuzzleTask
     * @covers \App\PuzzleTask::savePuzzleTask
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Puzzle\AbstractPuzzleGenerator::generate
     * @covers \App\Puzzle\AbstractPuzzleGenerator::generateChoices
     * @covers \App\Puzzle\AbstractPuzzleGenerator::getMaxChoicesCount
     * @covers \App\Puzzle\AbstractPuzzleGenerator::getRandomAnswer
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\RiddlePuzzleGenerator::generateQuestion
     * @covers \App\Puzzle\RiddlePuzzleGenerator::getAnswers
     * @covers \App\Puzzle\RiddlePuzzleGenerator::getChoiceByAnswer
     * @covers \App\Puzzle\RiddlePuzzleGenerator::getChosenAnswer
     * @covers \App\Puzzle\RiddlePuzzleGenerator::getRiddles
     * @covers \App\TelegramBotClient::__construct
     * @covers \App\TelegramBotClient::getNewMemberUpdateDto
     * @covers \App\TelegramBotClient::getUpdateDtos
     * @covers \App\TelegramBotClient::getUpdates
     * @covers \App\TelegramBotClient::getUserName
     * @covers \App\TelegramBotClient::isCorrectNewMemberUpdate
     * @covers \App\TelegramBotClient::muteUser
     * @covers \App\TelegramBotClient::sendKeyboardMarkupMessage
     * @covers \App\TelegramSettings::__construct
     * @covers \App\TelegramSettings::getMessageOffset
     * @covers \App\TelegramSettings::setMessageOffset
     * @covers \App\UpdateManager::run
     * @covers \App\UpdateProcessor\NewMembersProcessor::__construct
     * @covers \App\UpdateProcessor\NewMembersProcessor::processUpdate()
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::banNonApprovedMembers
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::getUpdateProcessorByUpdateType
     */
    public function testNewMemberUpdate(): void
    {
        $goodConfig = GoodConfigProvider::getGoodConfig();
        $goodConfigJson = json_encode($goodConfig[0][0]);

        $logger = $this->getMockLogger();
        $botApi = $this->getMockBotApi();

        $botUserType = $this->getMockBotUserType();
        $botApi->expects(self::once())
            ->method('getMe')
            ->willReturn($botUserType);

        $sendMessageType = $this->getMockSentPuzzleMessageType();
        $botApi->expects(self::once())
            ->method('send')
            ->willReturn($sendMessageType);

        $botApi->expects(self::once())
            ->method('restrict');

        $updateType = $this->getMockRegularUpdateType();
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
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\DatabaseService::init
     * @covers \App\Dto\BotSettingsDto::getBotUserName
     * @covers \App\Dto\BotSettingsDto::getIntroMessage
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::setIntroMessage
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setWelcomeMessage
     * @covers \App\Dto\NewMemberTelegramUpdateDto::__construct
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getChatId
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getNewMembers
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getType
     * @covers \App\Dto\NewMemberTelegramUpdateDto::getUpdateId
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\UserDto::__construct
     * @covers \App\Dto\UserDto::getUserName
     * @covers \App\PuzzleTask::__construct
     * @covers \App\PuzzleTask::getNonApprovedUsers
     * @covers \App\TelegramBotClient::__construct
     * @covers \App\TelegramBotClient::getNewMemberUpdateDto
     * @covers \App\TelegramBotClient::getUpdateDtos
     * @covers \App\TelegramBotClient::getUpdates
     * @covers \App\TelegramBotClient::getUserName
     * @covers \App\TelegramBotClient::isCorrectNewMemberUpdate
     * @covers \App\TelegramBotClient::sendChatMessage
     * @covers \App\TelegramSettings::__construct
     * @covers \App\TelegramSettings::getMessageOffset
     * @covers \App\TelegramSettings::setMessageOffset
     * @covers \App\UpdateManager::run
     * @covers \App\UpdateProcessor\NewMembersProcessor::__construct
     * @covers \App\UpdateProcessor\NewMembersProcessor::processUpdate
     * @covers \App\UpdateProcessor\NewMembersProcessor::sendIntroMessage
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::banNonApprovedMembers
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::getUpdateProcessorByUpdateType
     */
    public function testNewMemberBotUpdate(): void
    {
        $goodConfig = GoodConfigProvider::getGoodConfig();
        $goodConfigJson = json_encode($goodConfig[0][0]);

        $logger = $this->getMockLogger();
        $botApi = $this->getMockBotApi();

        $botUserType = $this->getMockBotUserType();
        $botApi->expects(self::once())
            ->method('getMe')
            ->willReturn($botUserType);

        $sendMessageType = $this->getMockSentPuzzleMessageType();
        $botApi->expects(self::once())
            ->method('send')
            ->willReturn($sendMessageType);

        $botApi->expects(self::never())
            ->method('restrict');

        $updateType = $this->getMockBotUpdateType();
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
     * @return BotApi|MockObject
     */
    private function getMockBotApi(): BotApi
    {
        $requestFactory = new RequestFactory();
        $streamFactory = new StreamFactory();
        $client = new Client();
        $apiClient = new ApiClient($requestFactory, $streamFactory, $client);
        $botApi = $this->getMockBuilder(BotApi::class)
            ->setConstructorArgs(
                [
                    'apikey', $apiClient, new BotApiNormalizer()
                ]
            )
            ->setMethods(['getUpdates', 'send', 'getMe', 'restrict'])
            ->getMock();

        return $botApi;
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
     * @return UpdateType
     */
    public function getMockRegularUpdateType(): UpdateType
    {
        $chat = new ChatType();
        $chat->id = 3;

        $message = new MessageType();
        $message->messageId = 2;
        $message->chat = $chat;

        $newMember = new UserType();
        $newMember->firstName = 'FirstName';
        $newMember->lastName = 'LastName';
        $newMember->username = 'UserName';
        $newMember->id = 4;

        $updateType = new UpdateType();
        $updateType->updateId = 1;
        $updateType->message = $message;
        $updateType->message->newChatMembers = [$newMember];

        return $updateType;
    }

    /**
     * @return UpdateType
     */
    public function getMockBotUpdateType(): UpdateType
    {
        $chat = new ChatType();
        $chat->id = 3;

        $message = new MessageType();
        $message->messageId = 2;
        $message->chat = $chat;

        $newMember = new UserType();
        $newMember->firstName = 'BotName';
        $newMember->lastName = 'BotName';
        $newMember->username = 'BotName';
        $newMember->id = 5;

        $updateType = new UpdateType();
        $updateType->updateId = 1;
        $updateType->message = $message;
        $updateType->message->newChatMembers = [$newMember];

        return $updateType;
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
     * @return MessageType
     */
    private function getMockSentPuzzleMessageType(): MessageType
    {
        $message = new MessageType();
        $message->messageId = 20;

        return $message;
    }
}
