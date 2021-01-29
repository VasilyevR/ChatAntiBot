<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Dto\BotSettingsDto;
use App\Dto\PuzzleAnswerTelegramUpdateDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\Exception\BotClientResponseException;
use App\PuzzleTask;
use App\TelegramBotClient;
use Psr\Log\LoggerInterface;
use SQLite3;

class PuzzleAnswerProcessor implements UpdateProcessorInterface
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
     * @inheritDoc
     * @param PuzzleAnswerTelegramUpdateDto $updateDto
     */
    public function processUpdate(TelegramUpdateDtoInterface $updateDto, BotSettingsDto $botSettingsDto): void
    {
        $userDto = $updateDto->getUser();
        if ($botSettingsDto->getBotUserName() !== $userDto->getUserName()) {
            return;
        }
        $puzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($updateDto->getChatId(), $userDto->getUserId());
        if (null === $puzzleTaskDto) {
            return;
        }
        try {
            $this->botClient->deleteMessage($updateDto->getChatId(), $updateDto->getPuzzleMessageId());
        } catch (BotClientResponseException $e) {
            $this->logger->warning(
                'Warning to delete puzzle message',
                [
                    'messageId' => $updateDto->getChatId(),
                    'errorCode' => $e->getCode(),
                    'error' => $e->getMessage()
                ]
            );
        }
        try {
            $this->botClient->deleteMessage($updateDto->getChatId(), $updateDto->getEnterMessageId());
        } catch (BotClientResponseException $e) {
            $this->logger->warning(
                'Warning to delete enter message',
                [
                    'messageId' => $updateDto->getChatId(),
                    'errorCode' => $e->getCode(),
                    'error' => $e->getMessage()
                ]
            );
        }
        $chatId = $puzzleTaskDto->getChatId();
        $userId = $puzzleTaskDto->getUserId();
        $this->puzzleTaskService->deletePuzzleTask($chatId, $userId);
        if ($updateDto->getAnswer() === $puzzleTaskDto->getAnswer()) {
            try {
                $this->botClient->unmuteUser($chatId, $userId);
            } catch (BotClientResponseException $e) {
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
        } catch (BotClientResponseException $e) {
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
}
