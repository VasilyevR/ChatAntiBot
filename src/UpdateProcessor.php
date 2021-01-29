<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use Psr\Log\LoggerInterface;
use SQLite3;
use TgBotApi\BotApiBase\Exception\ResponseException;

class UpdateProcessor
{
    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     * @param LoggerInterface $logger
     * @param BotSettingsDto $botSettingsDto
     */
    public function run(
        TelegramBotClient $botClient,
        SQLite3 $database,
        LoggerInterface $logger,
        BotSettingsDto $botSettingsDto
    ): void
    {
        $botSettingsService = new TelegramSettings($database);
        try {
            $updates = $botClient->getUpdates($botSettingsService->getMessageOffset());
        } catch (ResponseException $e) {
            $logger->error('Error to get updates', ['code' => $e->getCode(), 'error' => $e->getMessage()]);
            return;
        }
        if (empty($updates)) {
            return;
        }
        try {
            $botSettingsDto->setBotUserName($botClient->getUserName());
        } catch (ResponseException $e) {
            $logger->error('Error to get bot info', ['code' => $e->getCode(), 'error' => $e->getMessage()]);
            return;
        }
        $newMembersProcessor = new NewMembersProcessor($botClient, $database, $logger);
        $puzzleAnswerProcessor = new PuzzleAnswerProcessor($botClient, $database, $logger);
        $nonApprovedMemberProcessor = new NonApprovedMemberProcessor($botClient, $database, $logger);

        foreach ($updates as $update) {
            $message = $update->message;
            $botSettingsService->setMessageOffset($update->updateId + 1);
            if (null !== $message) {
                $newMembersProcessor->processMessage($message, $botSettingsDto);
            }
            $puzzleAnswerProcessor->processPuzzleAnswer($update, $botSettingsDto);
        }
        $nonApprovedMemberProcessor->banNonApprovedMembers($botSettingsDto);
    }
}
