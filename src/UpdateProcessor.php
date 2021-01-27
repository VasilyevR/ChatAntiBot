<?php
declare(strict_types=1);

namespace App;

use App\Enum\PuzzleTypeEnum;
use App\Puzzle\PuzzleFactory;
use SQLite3;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Method\DeleteMessageMethod;
use TgBotApi\BotApiBase\Method\GetMeMethod;
use TgBotApi\BotApiBase\Method\GetUpdatesMethod;
use TgBotApi\BotApiBase\Method\KickChatMemberMethod;
use TgBotApi\BotApiBase\Method\RestrictChatMemberMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;

class UpdateProcessor
{
    /**
     * @var BotApi
     */
    private $botApi;

    /**
     * @var SQLite3
     */
    private $database;

    /**
     * @var string
     */
    private $botUserName;

    /**
     * @var ChatSettings
     */
    private $chatSettingsService;

    /**
     * @var TelegramSettings
     */
    private $telegramSettingsService;

    /**
     * @var PuzzleTask
     */
    private $puzzleTaskService;

    /**
     * @var int
     */
    private $timeOutPuzzleReply;

    /**
     * @param BotApi $botApi
     * @param SQLite3 $database
     * @param int $timeOutPuzzleReply
     */
    public function __construct(BotApi $botApi, SQLite3 $database, int $timeOutPuzzleReply)
    {
        $this->botApi = $botApi;
        $this->database = $database;
        $this->timeOutPuzzleReply = $timeOutPuzzleReply;
    }

    //TODO: Catch ResponseException and save in log
    public function run()
    {
        $this->chatSettingsService = new ChatSettings($this->database);
        $this->telegramSettingsService = new TelegramSettings($this->database);
        $this->puzzleTaskService = new PuzzleTask($this->database);
        $botInfo = $this->getBotInfo();
        $this->botUserName = $botInfo->username;
        $updatesArray = $this->getUpdates();
        $this->processUpdates($updatesArray);
    }

    /**
     * @param UpdateType[] $updates
     * @throws ResponseException
     */
    private function processUpdates(array $updates): void
    {
        foreach ($updates as $update) {
            $message = $update->message;
            $this->telegramSettingsService->setMessageOffset($update->updateId + 1);
            if (null !== $message) {
                //TODO: Add commands to bot
                //$this->processCommands($message);
                $this->processNewChatMembers($message);
            }
            $this->processPuzzleAnswer($update);
        }
        $this->banNonApprovedMembers();
    }

    /**
     * @param MessageType $message
     * @throws ResponseException
     */
    private function processNewChatMembers(MessageType $message): void
    {
        $newChatMembers = $this->getNewChatMembers($message);
        if (empty($newChatMembers)) {
            return;
        }
        $chatId = $message->chat->id;
        $puzzleType = $this->chatSettingsService->getPuzzleType($chatId) ?? PuzzleTypeEnum::RIDDLES;
        foreach ($newChatMembers as $newChatMember) {
            if ($this->botUserName === $newChatMember->username) {
                $this->sendInitInformation($chatId, $this->timeOutPuzzleReply);
                continue;
            }
            $newChatMemberId = $newChatMember->id;
            $existPuzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($chatId, $newChatMemberId);
            if (null !== $existPuzzleTaskDto) {
                continue;
            }
            $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleType);
            $puzzleDto = $puzzleGenerator->generate();
            $this->puzzleTaskService->savePuzzleTask(
                $chatId,
                $newChatMemberId,
                $puzzleDto->getAnswer(),
                $message->messageId
            );
            $this->sendPuzzle(
                $chatId,
                $message->messageId,
                $puzzleDto->getQuestion(),
                $puzzleDto->getChoices()
            );
            $this->muteUser($chatId, $newChatMemberId);
        }
    }

    /**
     * @param UpdateType $update
     * @throws ResponseException
     */
    private function processPuzzleAnswer(UpdateType $update): void
    {
        if (!$this->needProcessPuzzleAnswerUpdate($update)) {
            return;
        }
        $replyToMessage = $update->callbackQuery->message->replyToMessage;
        $puzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($replyToMessage->chat->id, $replyToMessage->from->id);
        if (null === $puzzleTaskDto) {
            return;
        }
        $message = $update->callbackQuery->message;
        if ($update->callbackQuery->data === $puzzleTaskDto->getAnswer()) {
            $this->approveUser($puzzleTaskDto->getChatId(), $puzzleTaskDto->getUserId());
            $this->deleteMessage($message->chat->id, $message->messageId);
            $this->deleteMessage($replyToMessage->chat->id, $replyToMessage->messageId);
        }
    }

    /**
     * @param MessageType $message
     * @return UserType[]
     */
    private function getNewChatMembers(MessageType $message): array
    {
        if (null === $message) {
            return [];
        }

        return $message->newChatMembers ?? [];
    }

    private function banNonApprovedMembers(): void
    {
        $nonApprovedUsers = $this->puzzleTaskService->getNonApprovedUsers($this->timeOutPuzzleReply);

        foreach ($nonApprovedUsers as $taskUserDto) {
            $this->banUser($taskUserDto->getChatId(), $taskUserDto->getUserId());
            $this->puzzleTaskService->deletePuzzleTask($taskUserDto->getChatId(), $taskUserDto->getUserId());
        }
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    private function approveUser(int $chatId, int $userId): void
    {
        $this->unmuteUser($chatId, $userId);
        $this->puzzleTaskService->deletePuzzleTask($chatId, $userId);
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @param string $question
     * @param array $choices
     * @throws ResponseException
     */
    private function sendPuzzle(int $chatId, int $messageId, string $question, array $choices): void
    {
        $keyboardButtons = [];
        foreach ($choices as $key => $choice) {
            $button = new InlineKeyboardButtonType();
            $button->text = $choice;
            $button->callbackData = $choice;
            $keyboardButtons[] = $button;
        }
        $replyMarkUp = new InlineKeyboardMarkupType();
        $replyMarkUp->inlineKeyboard = [$keyboardButtons];
        $sendMessageMethod = SendMessageMethod::create($chatId, $question);
        $sendMessageMethod->replyMarkup = $replyMarkUp;
        $sendMessageMethod->disableNotification = true;
        $sendMessageMethod->replyToMessageId = $messageId;
        $sendMessageMethod->parseMode = 'Markdown';
        $this->botApi->send($sendMessageMethod);
    }

    /**
     * @return UpdateType[]
     * @throws ResponseException
     */
    private function getUpdates(): array
    {
        $getUpdates = new GetUpdatesMethod();
        $messageOffset = $this->telegramSettingsService->getMessageOffset();
        if ($messageOffset) {
            $getUpdates->offset = $messageOffset;
        }
        return $this->botApi->getUpdates($getUpdates);
    }

    /**
     * @param int $chatId
     * @param int $timeOut
     * @throws ResponseException
     */
    private function sendInitInformation(int $chatId, int $timeOut): void
    {
        $information = 'Здравствуйте! Я бот!
Я умею задавать входящим пользователям простые вопросы.
Тот кто не ответил на них правильно в течение %d минут вероятней всего бот.
Либо просто не захотел или не успел ответить.
Он будет удален мной из этого чата, но может быть добавлен любым админом.
Не забудьте меня сделать админом этого чата!';
        $text = sprintf($information, $timeOut);
        $sendMessageMethod = SendMessageMethod::create($chatId, $text);
        $this->botApi->send($sendMessageMethod);
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @throws ResponseException
     */
    private function deleteMessage(int $chatId, int $messageId): void
    {
        $sendMessageMethod = DeleteMessageMethod::create($chatId, $messageId);
        $this->botApi->delete($sendMessageMethod);
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    private function banUser(int $chatId, int $userId): void
    {
        $kickChatMemberMethod = KickChatMemberMethod::create($chatId, $userId);
        $this->botApi->kick($kickChatMemberMethod);
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    private function muteUser(int $chatId, int $userId): void
    {
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = false;
        $this->botApi->restrict($restrictChatMemberMethod);
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    private function unmuteUser(int $chatId, int $userId): void
    {
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = true;
        $this->botApi->restrict($restrictChatMemberMethod);
    }

    /**
     * @return UserType
     * @throws ResponseException
     */
    private function getBotInfo(): UserType
    {
        return $this->botApi->getMe(GetMeMethod::create());
    }

    /**
     * @param UpdateType $update
     * @return bool
     */
    private function needProcessPuzzleAnswerUpdate(UpdateType $update): bool
    {
        if (null === $update->callbackQuery
            || null === $update->callbackQuery->data
            || null === $update->callbackQuery->message
        ) {
            return false;
        }
        $message = $update->callbackQuery->message;
        if ($this->botUserName !== $message->from->username) {
            return false;
        }
        $replyToMessage = $update->callbackQuery->message->replyToMessage;
        if ($replyToMessage->from->id !== $update->callbackQuery->from->id) {
            return false;
        }
        return !(null === $replyToMessage || null === $replyToMessage->from);
    }
}
