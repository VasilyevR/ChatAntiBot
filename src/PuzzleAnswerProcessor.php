<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use SQLite3;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Type\UpdateType;

class PuzzleAnswerProcessor
{
    /**
     * @var TelegramBotClient
     */
    private $botClient;

    /**
     * @var PuzzleTask
     */
    private $puzzleTaskService;

    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database)
    {
        $this->botClient = $botClient;
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
        $this->botClient->deleteMessage($message->chat->id, $message->messageId);
        $this->botClient->deleteMessage($replyToMessage->chat->id, $replyToMessage->messageId);
        $this->puzzleTaskService->deletePuzzleTask($puzzleTaskDto->getChatId(), $puzzleTaskDto->getUserId());
        if ($update->callbackQuery->data === $puzzleTaskDto->getAnswer()) {
            $this->botClient->unmuteUser($puzzleTaskDto->getChatId(), $puzzleTaskDto->getUserId());
            return;
        }
        $this->botClient->banUser($puzzleTaskDto->getChatId(), $puzzleTaskDto->getUserId());
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
