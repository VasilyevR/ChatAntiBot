<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Dto\BotSettingsDto;
use App\Dto\PuzzleAnswerTelegramUpdateDto;
use App\Dto\PuzzleTaskUserDto;
use App\Exception\BotClientResponseException;
use App\PuzzleTask;
use App\TelegramBotClient;
use Psr\Log\LoggerInterface;
use SQLite3;

class NonApprovedMemberProcessor
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
     * @param BotSettingsDto $botSettingsDto
     */
    public function banNonApprovedMembers(BotSettingsDto $botSettingsDto): void
    {
        $nonApprovedUsers = $this->puzzleTaskService->getNonApprovedUsers($botSettingsDto->getPuzzleReplyTimeOut());

        foreach ($nonApprovedUsers as $taskUserDto) {
            $chatId = $taskUserDto->getChatId();
            $userId = $taskUserDto->getUserId();
            $this->deletePuzzleMessage($taskUserDto);
            try {
                $this->botClient->banUser($chatId, $userId);
            } catch (BotClientResponseException $exception) {
                $this->logger->error(
                    'Error to ban user by timeout',
                    [
                        'chatId' => $chatId,
                        'userId' => $userId,
                        'errorCode' => $exception->getCode(),
                        'error' => $exception->getMessage()
                    ]
                );
            }
            $this->puzzleTaskService->deletePuzzleTask($chatId, $userId);
        }
    }

    /**
     * @param PuzzleTaskUserDto $taskUserDto
     */
    private function deletePuzzleMessage(PuzzleTaskUserDto $taskUserDto): void
    {
        try {
            $this->botClient->deleteMessage($taskUserDto->getChatId(), $taskUserDto->getMessageId());
        } catch (BotClientResponseException $exception) {
            $this->logger->warning(
                'Warning to delete puzzle message',
                [
                    'messageId' => $taskUserDto->getMessageId(),
                    'errorCode' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            );
        }
    }
}
