<?php
declare(strict_types=1);

namespace App;

use App\Exception\UnknownUpdateProcessorException;
use App\Dto\BotSettingsDto;
use App\Exception\BotClientResponseException;
use App\UpdateProcessor\NonApprovedMemberProcessor;
use App\UpdateProcessor\UpdateProcessorManager;
use Psr\Log\LoggerInterface;
use SQLite3;

class UpdateManager
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
        } catch (BotClientResponseException $exception) {
            $logger->error('Error to get updates', ['code' => $exception->getCode(), 'error' => $exception->getMessage()]);
            return;
        }
        if (empty($updates)) {
            return;
        }
        try {
            $botSettingsDto->setBotUserName($botClient->getUserName());
        } catch (BotClientResponseException $exception) {
            $logger->error('Error to get bot info', ['code' => $exception->getCode(), 'error' => $exception->getMessage()]);
            return;
        }
        $updateProcessorManager = new UpdateProcessorManager($botClient, $database, $logger);
        $nonApprovedMemberProcessor = new NonApprovedMemberProcessor($botClient, $database, $logger);

        foreach ($updates as $updateDto) {
            $botSettingsService->setMessageOffset($updateDto->getUpdateId() + 1);
            try {
                $updateProcessor = $updateProcessorManager->getUpdateProcessorByUpdateType($updateDto->getType());
            } catch (UnknownUpdateProcessorException $exception) {
                $logger->error($exception->getMessage());
                return;
            }
            $updateProcessor->processUpdate($updateDto, $botSettingsDto);
        }
        $nonApprovedMemberProcessor->banNonApprovedMembers($botSettingsDto);
    }
}
