<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use SQLite3;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Method\KickChatMemberMethod;

class NonApprovedMemberProcessor
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
     * @param BotSettingsDto $botSettingsDto
     * @throws ResponseException
     */
    public function banNonApprovedMembers(BotSettingsDto $botSettingsDto): void
    {
        $nonApprovedUsers = $this->puzzleTaskService->getNonApprovedUsers($botSettingsDto->getTimeOutPuzzleReply());

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
    private function banUser(int $chatId, int $userId): void
    {
        $kickChatMemberMethod = KickChatMemberMethod::create($chatId, $userId);
        $this->botApi->kick($kickChatMemberMethod);
    }
}
