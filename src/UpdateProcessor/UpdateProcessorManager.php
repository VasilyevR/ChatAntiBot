<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Exception\UnknownUpdateProcessorException;
use App\TelegramBotClient;
use App\Enum\TelegramUpdateEnum;
use Psr\Log\LoggerInterface;
use SQLite3;

class UpdateProcessorManager
{
    private $puzzleAnswerProcessor;
    private $newMemberProcessor;

    public function __construct(TelegramBotClient $botClient, SQLite3 $database, LoggerInterface $logger)
    {
        $this->puzzleAnswerProcessor = new PuzzleAnswerProcessor($botClient, $database, $logger);
        $this->newMemberProcessor = new NewMembersProcessor($botClient, $database, $logger);
    }

    /**
     * @param string $updateType
     * @return UpdateProcessorInterface
     * @throws UnknownUpdateProcessorException
     */
    public function getUpdateProcessorByUpdateType(string $updateType): UpdateProcessorInterface
    {
        switch ($updateType) {
            case TelegramUpdateEnum::PUZZLE_ANSWER:
                return $this->puzzleAnswerProcessor;
            case TelegramUpdateEnum::NEW_MEMBER:
                return $this->newMemberProcessor;
            default:
                throw new UnknownUpdateProcessorException(
                    sprintf('Error to select update processor by type %s', $updateType)
                );
        }
    }
}
