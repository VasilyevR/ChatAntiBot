<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Dto\BotSettingsDto;
use App\Dto\NewMemberTelegramUpdateDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\Exception\BotClientResponseException;
use App\Puzzle\PuzzleFactory;
use App\PuzzleTask;
use App\TelegramBotClient;
use Psr\Log\LoggerInterface;
use SQLite3;

class NewMembersProcessor implements UpdateProcessorInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     * @param LoggerInterface $logger
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database, LoggerInterface $logger)
    {
        $this->botClient = $botClient;
        $this->puzzleTaskService = new PuzzleTask($database);
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     * @param NewMemberTelegramUpdateDto $updateDto
     */
    public function processUpdate(TelegramUpdateDtoInterface $updateDto, BotSettingsDto $botSettingsDto): void
    {
        $newChatMembers = $updateDto->getNewMembers();
        if (empty($newChatMembers)) {
            return;
        }
        $chatId = $updateDto->getChatId();
        foreach ($newChatMembers as $newChatMember) {
            if ($newChatMember->getUserName() === $botSettingsDto->getBotUserName()) {
                $this->sendIntroMessage(
                    $chatId,
                    $botSettingsDto->getIntroMessage(),
                $botSettingsDto->getPuzzleReplyTimeOut()
                );
                continue;
            }
            $newChatMemberId = $newChatMember->getUserId();
            $existPuzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($chatId, $newChatMemberId);
            if (null !== $existPuzzleTaskDto) {
                continue;
            }
            $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($botSettingsDto->getPuzzlesSettings());
            $puzzleDto = $puzzleGenerator->generate();
            try {
                $messageSent = $this->botClient->sendKeyboardMarkupMessage(
                    $chatId,
                    $updateDto->getMessageId(),
                    $puzzleDto->getQuestion(),
                    $puzzleDto->getChoices()
                );
            } catch (BotClientResponseException $exception) {
                $this->logger->error(
                    'Error to send puzzle',
                    [
                        'chatId' => $chatId,
                        'messageId' => $updateDto->getMessageId(),
                        'errorCode' => $exception->getCode(),
                        'error' => $exception->getMessage()
                    ]
                );
                continue;
            }
            $this->puzzleTaskService->savePuzzleTask(
                $chatId,
                $newChatMemberId,
                $puzzleDto->getAnswer(),
                $updateDto->getMessageId(),
                $messageSent->messageId
            );
            try {
                $this->botClient->muteUser($chatId, $newChatMemberId);
            } catch (BotClientResponseException $exception) {
                $this->logger->error(
                    'Error to mute new member',
                    [
                        'chatId' => $chatId,
                        'userId' => $newChatMemberId,
                        'errorCode' => $exception->getCode(),
                        'error' => $exception->getMessage()
                    ]
                );
            }
        }
    }

    /**
     * @param int $chatId
     * @param string $introMessage
     * @param int $timeOut
     */
    private function sendIntroMessage(int $chatId, string $introMessage, int $timeOut): void
    {
        $text = sprintf($introMessage, $timeOut);
        try {
            $this->botClient->sendChatMessage($chatId, $text);
        } catch (BotClientResponseException $exception) {
            $this->logger->warning(
                'Warning to send init message',
                [
                    'chatId' => $chatId,
                    'errorCode' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            );
        }
    }
}
