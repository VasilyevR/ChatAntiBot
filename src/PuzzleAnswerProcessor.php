<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     * @param LoggerInterface $logger
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database, LoggerInterface $logger)
    {
        $this->botClient = $botClient;
        $this->puzzleTaskService = new PuzzleTask($database);
        $this->logger = $logger;
    }

    /**
     * @param UpdateType $update
     * @param BotSettingsDto $botSettingsDto
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
        try {
            $this->botClient->deleteMessage($message->chat->id, $message->messageId);
        } catch (ResponseException $e) {
            $this->logger->warning(
                'Warning to delete puzzle message',
                [
                    'messageId' => $message->chat->id,
                    'errorCode' => $e->getCode(),
                    'error' => $e->getMessage()
                ]
            );
        }
        try {
            $this->botClient->deleteMessage($replyToMessage->chat->id, $replyToMessage->messageId);
        } catch (ResponseException $e) {
            $this->logger->warning(
                'Warning to delete enter message',
                [
                    'messageId' => $replyToMessage->chat->id,
                    'errorCode' => $e->getCode(),
                    'error' => $e->getMessage()
                ]
            );
        }
        $chatId = $puzzleTaskDto->getChatId();
        $userId = $puzzleTaskDto->getUserId();
        $this->puzzleTaskService->deletePuzzleTask($chatId, $userId);
        if ($update->callbackQuery->data === $puzzleTaskDto->getAnswer()) {
            try {
                $this->botClient->unmuteUser($chatId, $userId);
            } catch (ResponseException $e) {
                $this->logger->error(
                    'Error to unmute new member',
                    [
                        'chatId' => $chatId,
                        'userId' => $userId,
                        'errorCode' => $e->getCode(),
                        'error' => $e->getMessage()
                    ]
                );
            }
            return;
        }
        try {
            $this->botClient->banUser($chatId, $userId);
        } catch (ResponseException $e) {
            $this->logger->error(
                'Error to ban user with wrong answer',
                [
                    'chatId' => $chatId,
                    'userId' => $userId,
                    'errorCode' => $e->getCode(),
                    'error' => $e->getMessage()
                ]
            );
        }
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
