<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Dto\BotSettingsDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\TelegramBotClient;
use Psr\Log\LoggerInterface;
use SQLite3;

interface UpdateProcessorInterface
{
    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     * @param LoggerInterface $logger
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database, LoggerInterface $logger);

    /**
     * @param TelegramUpdateDtoInterface $updateDto
     * @param BotSettingsDto $botSettingsDto
     */
    public function processUpdate(TelegramUpdateDtoInterface $updateDto, BotSettingsDto $botSettingsDto): void;
}
