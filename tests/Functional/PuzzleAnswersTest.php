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
use TgBotApi\BotApiBase\Type\CallbackQueryType;
use TgBotApi\BotApiBase\Type\ChatType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;

class PuzzleAnswersTest extends TestCase
{
    private const CHAT_ID = 3;
    private const USER_ID = 5;
    private const GOOD_ANSWER = 'good_answer';
    private const WRONG_ANSWER = 'wrong_answer';
    private const MESSAGE_ENTER_ID = 7;
    private const MESSAGE_PUZZLE_ID = 8;
    private const MESSAGE_ANSWER_ID = 9;
    private const MESSAGE_WELCOME_ID = 10;

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\DatabaseService::init
     * @covers \App\Dto\BotSettingsDto::getBotUserName
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::getWelcomeMessage
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::setIntroMessage
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setWelcomeMessage
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getAnswer
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getChatId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getEnterMessageId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getPuzzleMessageId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getType
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUpdateId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUser
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleTaskDto::__construct
     * @covers \App\Dto\PuzzleTaskDto::getAnswer
     * @covers \App\Dto\PuzzleTaskDto::getChatId
     * @covers \App\Dto\PuzzleTaskDto::getTaskMessageId
     * @covers \App\Dto\PuzzleTaskDto::getUserId
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\UserDto::__construct
     * @covers \App\Dto\UserDto::getUserId
     * @covers \App\Dto\UserDto::getUserName
     * @covers \App\PuzzleTask::__construct
     * @covers \App\PuzzleTask::deletePuzzleTask
     * @covers \App\PuzzleTask::getNonApprovedUsers
     * @covers \App\PuzzleTask::getPuzzleTask
     * @covers \App\TelegramBotClient::__construct
     * @covers \App\TelegramBotClient::deleteMessage
     * @covers \App\TelegramBotClient::getPuzzleAnswerUpdateDto
     * @covers \App\TelegramBotClient::getUpdateDtos
     * @covers \App\TelegramBotClient::getUpdates
     * @covers \App\TelegramBotClient::getUserName
     * @covers \App\TelegramBotClient::isCorrectNewMemberUpdate
     * @covers \App\TelegramBotClient::isCorrectPuzzleAnswerUpdate
     * @covers \App\TelegramBotClient::sendChatMessage
     * @covers \App\TelegramBotClient::unmuteUser
     * @covers \App\TelegramSettings::__construct
     * @covers \App\TelegramSettings::getMessageOffset
     * @covers \App\TelegramSettings::setMessageOffset
     * @covers \App\UpdateManager::run
     * @covers \App\UpdateProcessor\NewMembersProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::banNonApprovedMembers
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::__construct
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::deletePuzzleMessage
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::processUpdate
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::sendWelcomeMessage
     * @covers \App\UpdateProcessor\UnnecessaryProcessor::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::getUpdateProcessorByUpdateType
     */
    public function testRightAnswer(): void
    {
        $goodConfig = GoodConfigProvider::getGoodConfig();
        $goodConfigJson = json_encode($goodConfig[0][0]);

        $logger = $this->getMockLogger();
        $botApi = $this->getMockBotApi();

        $updateType = $this->getMockRightAnswerUpdateType();
        $botApi->expects(self::once())
            ->method('getUpdates')
            ->willReturn([$updateType]);

        $botUserType = $this->getMockBotUserType();
        $botApi->expects(self::once())
            ->method('getMe')
            ->willReturn($botUserType);

        $botApi->expects(self::once())
            ->method('delete');

        $botApi->expects(self::once())
            ->method('restrict');

        $botApi->expects(self::never())
            ->method('kick');

        $sendMessageType = $this->getMockSentWelcomeMessageType();
        $botApi->expects(self::once())
            ->method('send')
            ->willReturn($sendMessageType);

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
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\DatabaseService::init
     * @covers \App\Dto\BotSettingsDto::getBotUserName
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::getPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::setIntroMessage
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setWelcomeMessage
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getAnswer
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getChatId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getEnterMessageId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getPuzzleMessageId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getType
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUpdateId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUser
     * @covers \App\Dto\PuzzleDto::__construct
     * @covers \App\Dto\PuzzleDto::getAnswer
     * @covers \App\Dto\PuzzleDto::getChoices
     * @covers \App\Dto\PuzzleDto::getQuestion
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzleTaskDto::__construct
     * @covers \App\Dto\PuzzleTaskDto::getAnswer
     * @covers \App\Dto\PuzzleTaskDto::getAttempt
     * @covers \App\Dto\PuzzleTaskDto::getChatId
     * @covers \App\Dto\PuzzleTaskDto::getTaskMessageId
     * @covers \App\Dto\PuzzleTaskDto::getUserId
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     * @covers \App\Dto\UserDto::__construct
     * @covers \App\Dto\UserDto::getUserId
     * @covers \App\Dto\UserDto::getUserName
     * @covers \App\PuzzleTask::__construct
     * @covers \App\PuzzleTask::deletePuzzleTask
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
     * @covers \App\TelegramBotClient::deleteMessage
     * @covers \App\TelegramBotClient::getPuzzleAnswerUpdateDto
     * @covers \App\TelegramBotClient::getUpdateDtos
     * @covers \App\TelegramBotClient::getUpdates
     * @covers \App\TelegramBotClient::getUserName
     * @covers \App\TelegramBotClient::isCorrectNewMemberUpdate
     * @covers \App\TelegramBotClient::isCorrectPuzzleAnswerUpdate
     * @covers \App\TelegramBotClient::sendKeyboardMarkupMessage
     * @covers \App\TelegramSettings::__construct
     * @covers \App\TelegramSettings::getMessageOffset
     * @covers \App\TelegramSettings::setMessageOffset
     * @covers \App\UpdateManager::run
     * @covers \App\UpdateProcessor\NewMembersProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::banNonApprovedMembers
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::__construct
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::deletePuzzleMessage
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::processUpdate
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::resendPuzzleMessage
     * @covers \App\UpdateProcessor\UnnecessaryProcessor::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::getUpdateProcessorByUpdateType
     */
    public function testWrongAnswer(): void
    {
        $goodConfig = GoodConfigProvider::getGoodConfig();
        $goodConfigJson = json_encode($goodConfig[0][0]);

        $logger = $this->getMockLogger();
        $botApi = $this->getMockBotApi();

        $updateType = $this->getMockWrongAnswerUpdateType();
        $botApi->expects(self::once())
            ->method('getUpdates')
            ->willReturn([$updateType]);

        $botUserType = $this->getMockBotUserType();
        $botApi->expects(self::once())
            ->method('getMe')
            ->willReturn($botUserType);

        $botApi->expects(self::once())
            ->method('delete');

        $botApi->expects(self::never())
            ->method('restrict');

        $botApi->expects(self::never())
            ->method('kick');

        $sendMessageType = $this->getMockSentPuzzleMessageType();
        $botApi->expects(self::once())
            ->method('send')
            ->willReturn($sendMessageType);

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
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\DatabaseService::init
     * @covers \App\Dto\BotSettingsDto::getBotUserName
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::setIntroMessage
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::setWelcomeMessage
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::__construct
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getAnswer
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getChatId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getEnterMessageId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getPuzzleMessageId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getType
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUpdateId
     * @covers \App\Dto\PuzzleAnswerTelegramUpdateDto::getUser
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleTaskDto::__construct
     * @covers \App\Dto\PuzzleTaskDto::getAnswer
     * @covers \App\Dto\PuzzleTaskDto::getAttempt
     * @covers \App\Dto\PuzzleTaskDto::getChatId
     * @covers \App\Dto\PuzzleTaskDto::getTaskMessageId
     * @covers \App\Dto\PuzzleTaskDto::getUserId
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\UserDto::__construct
     * @covers \App\Dto\UserDto::getUserId
     * @covers \App\Dto\UserDto::getUserName
     * @covers \App\PuzzleTask::__construct
     * @covers \App\PuzzleTask::deletePuzzleTask
     * @covers \App\PuzzleTask::getNonApprovedUsers
     * @covers \App\PuzzleTask::getPuzzleTask
     * @covers \App\TelegramBotClient::__construct
     * @covers \App\TelegramBotClient::banUser
     * @covers \App\TelegramBotClient::deleteMessage
     * @covers \App\TelegramBotClient::getPuzzleAnswerUpdateDto
     * @covers \App\TelegramBotClient::getUpdateDtos
     * @covers \App\TelegramBotClient::getUpdates
     * @covers \App\TelegramBotClient::getUserName
     * @covers \App\TelegramBotClient::isCorrectNewMemberUpdate
     * @covers \App\TelegramBotClient::isCorrectPuzzleAnswerUpdate
     * @covers \App\TelegramSettings::__construct
     * @covers \App\TelegramSettings::getMessageOffset
     * @covers \App\TelegramSettings::setMessageOffset
     * @covers \App\UpdateManager::run
     * @covers \App\UpdateProcessor\NewMembersProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::__construct
     * @covers \App\UpdateProcessor\NonApprovedMemberProcessor::banNonApprovedMembers
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::__construct
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::deletePuzzleMessage
     * @covers \App\UpdateProcessor\PuzzleAnswerProcessor::processUpdate
     * @covers \App\UpdateProcessor\UnnecessaryProcessor::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::__construct
     * @covers \App\UpdateProcessor\UpdateProcessorManager::getUpdateProcessorByUpdateType     */
    public function testWrongAnswerTwice(): void
    {
        $goodConfig = GoodConfigProvider::getGoodConfig();
        $goodConfigJson = json_encode($goodConfig[0][0]);

        $logger = $this->getMockLogger();
        $botApi = $this->getMockBotApi();

        $updateType = $this->getMockWrongAnswerUpdateType();
        $botApi->expects(self::once())
            ->method('getUpdates')
            ->willReturn([$updateType]);

        $botUserType = $this->getMockBotUserType();
        $botApi->expects(self::once())
            ->method('getMe')
            ->willReturn($botUserType);

        $botApi->expects(self::once())
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
        $this->insertSecondPuzzleTask($database);
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

        return $this->getMockBuilder(BotApi::class)
            ->setConstructorArgs(
                [
                    'apikey', $apiClient, new BotApiNormalizer()
                ]
            )
            ->setMethods(['getUpdates', 'send', 'getMe', 'restrict', 'kick', 'delete'])
            ->getMock();
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
    public function getMockRightAnswerUpdateType(): UpdateType
    {
        $chat = new ChatType();
        $chat->id = self::CHAT_ID;

        $newMember = new UserType();
        $newMember->id = self::USER_ID;
        $newMember->firstName = 'FirstName';
        $newMember->lastName = 'LastName';
        $newMember->username = 'UserName';

        $enterMessage = new MessageType();
        $enterMessage->chat = $chat;
        $enterMessage->messageId = self::MESSAGE_ENTER_ID;
        $enterMessage->from = $newMember;

        $puzzleMessage = new MessageType();
        $puzzleMessage->messageId = self::MESSAGE_PUZZLE_ID;
        $puzzleMessage->chat = $chat;
        $puzzleMessage->replyToMessage = $enterMessage;

        $callBackQuery = new CallbackQueryType();
        $callBackQuery->id = self::MESSAGE_ANSWER_ID;
        $callBackQuery->message = $puzzleMessage;
        $callBackQuery->from = $newMember;
        $callBackQuery->data = self::GOOD_ANSWER;

        $updateType = new UpdateType();
        $updateType->updateId = 1;
        $updateType->message = $puzzleMessage;
        $updateType->callbackQuery = $callBackQuery;

        return $updateType;
    }

    /**
     * @return UpdateType
     */
    public function getMockWrongAnswerUpdateType(): UpdateType
    {
        $chat = new ChatType();
        $chat->id = self::CHAT_ID;

        $newMember = new UserType();
        $newMember->id = self::USER_ID;
        $newMember->firstName = 'FirstName';
        $newMember->lastName = 'LastName';
        $newMember->username = 'UserName';

        $enterMessage = new MessageType();
        $enterMessage->chat = $chat;
        $enterMessage->messageId = self::MESSAGE_ENTER_ID;
        $enterMessage->from = $newMember;

        $puzzleMessage = new MessageType();
        $puzzleMessage->messageId = self::MESSAGE_PUZZLE_ID;
        $puzzleMessage->chat = $chat;
        $puzzleMessage->replyToMessage = $enterMessage;

        $callBackQuery = new CallbackQueryType();
        $callBackQuery->id = self::MESSAGE_ANSWER_ID;
        $callBackQuery->message = $puzzleMessage;
        $callBackQuery->from = $newMember;
        $callBackQuery->data = self::WRONG_ANSWER;

        $updateType = new UpdateType();
        $updateType->updateId = 1;
        $updateType->message = $puzzleMessage;
        $updateType->callbackQuery = $callBackQuery;

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
     * @param SQLite3 $database
     */
    private function insertPuzzleTask(SQLite3 $database): void
    {
        $database->query(
            sprintf("INSERT INTO puzzle_task (chat_id, user_id, answer, message_id, puzzle_id, attempt, date_time)
     VALUES (%d, %d, '%s', '%s', '%s', 0, CURRENT_TIMESTAMP)",
                    self::CHAT_ID,
                    self::USER_ID,
                    self::GOOD_ANSWER,
                    self::MESSAGE_ENTER_ID,
                    self::MESSAGE_PUZZLE_ID)
        );
    }

    /**
     * @param SQLite3 $database
     */
    private function insertSecondPuzzleTask(SQLite3 $database): void
    {
        $database->query(
            sprintf("INSERT INTO puzzle_task (chat_id, user_id, answer, message_id, puzzle_id, attempt, date_time)
     VALUES (%d, %d, '%s', '%s', '%s', 1, CURRENT_TIMESTAMP)",
                    self::CHAT_ID,
                    self::USER_ID,
                    self::GOOD_ANSWER,
                    self::MESSAGE_ENTER_ID,
                    self::MESSAGE_PUZZLE_ID)
        );
    }

    /**
     * @return MessageType
     */
    private function getMockSentWelcomeMessageType(): MessageType
    {
        $message = new MessageType();
        $message->messageId = self::MESSAGE_WELCOME_ID;

        return $message;
    }

    /**
     * @return MessageType
     */
    private function getMockSentPuzzleMessageType(): MessageType
    {
        $message = new MessageType();
        $message->messageId = self::MESSAGE_PUZZLE_ID;

        return $message;
    }
}
