<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use SQLite3;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Method\DeleteMessageMethod;
use TgBotApi\BotApiBase\Method\KickChatMemberMethod;
use TgBotApi\BotApiBase\Method\RestrictChatMemberMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class PuzzleAnswerProcessor
{
    /**
     * @var BotApi
     */
    private $botApi;

    /**
     * @var PuzzleTask
     */
    private $puzzleTaskService;

    /**
     * @param BotApi $botApi
     * @param SQLite3 $database
     */
    public function __construct(BotApi $botApi, SQLite3 $database)
    {
        $this->botApi = $botApi;
        $this->puzzleTaskService = new PuzzleTask($database);
    }

    /**
     * @param UpdateType $update
     * @param BotSettingsDto $botSettingsDto
     * @throws ResponseException
     */
    public function processPuzzleAnswer(UpdateType $update, BotSettingsDto $botSettingsDto): void
    {
        if (!$this->isCorrectPuzzleAnswerUpdate($update, $botSettingsDto->getBotUserName())) {
            return;
        }
        $replyToMessage = $update->callbackQuery->message->replyToMessage;
        $puzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($replyToMessage->chat->id, $replyToMessage->from->id);
        if (null === $puzzleTaskDto) {
            return;
        }
        $message = $update->callbackQuery->message;
        $this->deleteMessage($message->chat->id, $message->messageId);
        $this->deleteMessage($replyToMessage->chat->id, $replyToMessage->messageId);
        $this->puzzleTaskService->deletePuzzleTask($puzzleTaskDto->getChatId(), $puzzleTaskDto->getUserId());
        if ($update->callbackQuery->data === $puzzleTaskDto->getAnswer()) {
            $this->unmuteUser($puzzleTaskDto->getChatId(), $puzzleTaskDto->getUserId());
            return;
        }
        $this->banUser($puzzleTaskDto->getChatId(), $puzzleTaskDto->getUserId());
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
    private function unmuteUser(int $chatId, int $userId): void
    {
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = true;
        $this->botApi->restrict($restrictChatMemberMethod);
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
     * @param UpdateType $update
     * @param string|null $botUserName
     * @return bool
     */
    private function isCorrectPuzzleAnswerUpdate(UpdateType $update, ?string $botUserName): bool
    {
        if (null === $update->callbackQuery
            || null === $update->callbackQuery->data
            || null === $update->callbackQuery->message
        ) {
            return false;
        }
        $message = $update->callbackQuery->message;
        if ($botUserName !== $message->from->username) {
            return false;
        }
        $replyToMessage = $update->callbackQuery->message->replyToMessage;
        if ($replyToMessage->from->id !== $update->callbackQuery->from->id) {
            return false;
        }
        return !(null === $replyToMessage || null === $replyToMessage->from);
    }
}
