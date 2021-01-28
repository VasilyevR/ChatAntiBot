<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use SQLite3;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Method\GetMeMethod;
use TgBotApi\BotApiBase\Method\GetUpdatesMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

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
     * @param BotApi $botApi
     * @param SQLite3 $database
     */
    public function __construct(BotApi $botApi, SQLite3 $database)
    {
        $this->botApi = $botApi;
        $this->database = $database;
    }

    //TODO: Catch ResponseException and save in log
    /**
     * @param BotSettingsDto $botSettingsDto
     */
    public function run(BotSettingsDto $botSettingsDto): void
    {
        $botSettingsService = new TelegramSettings($this->database);
        $updates = $this->getUpdates($botSettingsService);
        if (empty($updates)) {
            return;
        }
        $botInfo = $this->botApi->getMe(GetMeMethod::create());
        $botSettingsDto->setBotUserName($botInfo->username);

        $newMembersProcessor = new NewMembersProcessor($this->botApi, $this->database);
        $puzzleAnswerProcessor = new PuzzleAnswerProcessor($this->botApi, $this->database);
        $nonApprovedMemberProcessor = new NonApprovedMemberProcessor($this->botApi, $this->database);

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

    /**
     * @param TelegramSettings $botSettingsService
     * @return UpdateType[]
     * @throws ResponseException
     */
    private function getUpdates(TelegramSettings $botSettingsService): array
    {
        $getUpdates = new GetUpdatesMethod();
        $messageOffset = $botSettingsService->getMessageOffset();
        if ($messageOffset) {
            $getUpdates->offset = $messageOffset;
        }
        return $this->botApi->getUpdates($getUpdates);
    }
}
