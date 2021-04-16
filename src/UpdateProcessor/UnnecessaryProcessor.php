<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Dto\BotSettingsDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\TelegramBotClient;
use Psr\Log\LoggerInterface;
use SQLite3;

class UnnecessaryProcessor implements UpdateProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database, LoggerInterface $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function processUpdate(TelegramUpdateDtoInterface $updateDto, BotSettingsDto $botSettingsDto): void
    {
        return;
    }
}
