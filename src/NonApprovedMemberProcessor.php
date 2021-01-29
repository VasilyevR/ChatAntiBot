<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
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
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database)
    {
        $this->botClient = $botClient;
        $this->puzzleTaskService = new PuzzleTask($database);
    }

    /**
     * @param BotSettingsDto $botSettingsDto
     * @throws ResponseException
     */
    public function banNonApprovedMembers(BotSettingsDto $botSettingsDto): void
    {
        $nonApprovedUsers = $this->puzzleTaskService->getNonApprovedUsers($botSettingsDto->getTimeOutPuzzleReply());

        foreach ($nonApprovedUsers as $taskUserDto) {
            $this->botClient->banUser($taskUserDto->getChatId(), $taskUserDto->getUserId());
            $this->puzzleTaskService->deletePuzzleTask($taskUserDto->getChatId(), $taskUserDto->getUserId());
        }
    }
}
