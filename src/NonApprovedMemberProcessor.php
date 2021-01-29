<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use Psr\Log\LoggerInterface;
use SQLite3;
use TgBotApi\BotApiBase\Exception\ResponseException;

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
        $nonApprovedUsers = $this->puzzleTaskService->getNonApprovedUsers($botSettingsDto->getTimeOutPuzzleReply());

        foreach ($nonApprovedUsers as $taskUserDto) {
            $chatId = $taskUserDto->getChatId();
            $userId = $taskUserDto->getUserId();
            try {
                $this->botClient->banUser($chatId, $userId);
            } catch (ResponseException $e) {
                $this->logger->error(
                    'Error to ban user by timeout',
                    [
                        'chatId' => $chatId,
                        'userId' => $userId,
                        'errorCode' => $e->getCode(),
                        'error' => $e->getMessage()
                    ]
                );
            }
            $this->puzzleTaskService->deletePuzzleTask($chatId, $userId);
        }
    }
}
