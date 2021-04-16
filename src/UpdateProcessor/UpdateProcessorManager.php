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
    /**
     * @var PuzzleAnswerProcessor
     */
    private $answersProcessor;

    /**
     * @var NewMembersProcessor
     */
    private $newMemberProcessor;

    /**
     * @var UnnecessaryProcessor
     */
    private $unnecesseryProcessor;

    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     * @param LoggerInterface $logger
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database, LoggerInterface $logger)
    {
        $this->answersProcessor = new PuzzleAnswerProcessor($botClient, $database, $logger);
        $this->newMemberProcessor = new NewMembersProcessor($botClient, $database, $logger);
        $this->unnecesseryProcessor = new UnnecessaryProcessor($botClient, $database, $logger);
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
                return $this->answersProcessor;
            case TelegramUpdateEnum::NEW_MEMBER:
                return $this->newMemberProcessor;
            case TelegramUpdateEnum::UNNECESSARY:
                return $this->unnecesseryProcessor;
            default:
                throw new UnknownUpdateProcessorException(
                    sprintf('Error to select update processor by type %s', $updateType)
                );
        }
    }
}
