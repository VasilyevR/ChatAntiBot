<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use SQLite3;

class UpdateProcessor
{
    /**
     * @var TelegramBotClient
     */
    private $botClient;

    /**
     * @var SQLite3
     */
    private $database;

    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database)
    {
        $this->botClient = $botClient;
        $this->database = $database;
    }

    //TODO: Catch ResponseException and save in log
    /**
     * @param BotSettingsDto $botSettingsDto
     */
    public function run(BotSettingsDto $botSettingsDto): void
    {
        $botSettingsService = new TelegramSettings($this->database);
        $updates = $this->botClient->getUpdates($botSettingsService->getMessageOffset());
        if (empty($updates)) {
            return;
        }
        $botSettingsDto->setBotUserName($this->botClient->getUserName());
        $newMembersProcessor = new NewMembersProcessor($this->botClient, $this->database);
        $puzzleAnswerProcessor = new PuzzleAnswerProcessor($this->botClient, $this->database);
        $nonApprovedMemberProcessor = new NonApprovedMemberProcessor($this->botClient, $this->database);

        foreach ($updates as $update) {
            $message = $update->message;
            $botSettingsService->setMessageOffset($update->updateId + 1);
            if (null !== $message) {
                //TODO: Add commands to bot
                $newMembersProcessor->processMessage($message, $botSettingsDto);
            }
            $puzzleAnswerProcessor->processPuzzleAnswer($update, $botSettingsDto);
        }
        $nonApprovedMemberProcessor->banNonApprovedMembers($botSettingsDto);
    }
}
