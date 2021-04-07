<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Dto\BotSettingsDto;
use App\Dto\PuzzleAnswerTelegramUpdateDto;
use App\Dto\PuzzleTaskDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\Exception\BotClientResponseException;
use App\Puzzle\PuzzleFactory;
use App\PuzzleTask;
use App\TelegramBotClient;
use Psr\Log\LoggerInterface;
use SQLite3;

class PuzzleAnswerProcessor implements UpdateProcessorInterface
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
     * @param PuzzleAnswerTelegramUpdateDto $updateDto
     */
    public function processUpdate(TelegramUpdateDtoInterface $updateDto, BotSettingsDto $botSettingsDto): void
    {
        $userDto = $updateDto->getUser();
        if ($botSettingsDto->getBotUserName() === $userDto->getUserName()) {
            return;
        }
        $puzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($updateDto->getChatId(), $userDto->getUserId());
        if (null === $puzzleTaskDto) {
            return;
        }
        if ($updateDto->getEnterMessageId() !== $puzzleTaskDto->getTaskMessageId()) {
            return;
        }
        $chatId = $puzzleTaskDto->getChatId();
        $userId = $puzzleTaskDto->getUserId();
        $this->puzzleTaskService->deletePuzzleTask($chatId, $userId);
        if ($updateDto->getAnswer() === $puzzleTaskDto->getAnswer()) {
            try {
                $this->botClient->unmuteUser($chatId, $userId);
            } catch (BotClientResponseException $exception) {
                $this->logger->error(
                    'Error to unmute new member',
                    [
                        'chatId' => $chatId,
                        'userId' => $userId,
                        'errorCode' => $exception->getCode(),
                        'error' => $exception->getMessage()
                    ]
                );
            }
            $this->deletePuzzleMessage($updateDto);
            $this->deleteEnterMessage($updateDto);
            $this->sendWelcomeMessage($updateDto, $botSettingsDto->getWelcomeMessage());
            return;
        }
        if ($puzzleTaskDto->getAttempt() < $botSettingsDto->getPuzzleReplyAttemptCount()) {
            $this->deletePuzzleMessage($updateDto);
            $this->resendPuzzleMessage($puzzleTaskDto, $botSettingsDto);
            return;
        }
        try {
            $this->botClient->banUser($chatId, $userId);
        } catch (BotClientResponseException $exception) {
            $this->logger->error(
                'Error to ban user with wrong answer',
                [
                    'chatId' => $chatId,
                    'userId' => $userId,
                    'errorCode' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            );
        }
        $this->deletePuzzleMessage($updateDto);
        $this->deleteEnterMessage($updateDto);
    }

    /**
     * @param PuzzleAnswerTelegramUpdateDto $updateDto
     */
    private function deletePuzzleMessage(PuzzleAnswerTelegramUpdateDto $updateDto): void
    {
        try {
            $this->botClient->deleteMessage($updateDto->getChatId(), $updateDto->getPuzzleMessageId());
        } catch (BotClientResponseException $exception) {
            $this->logger->warning(
                'Warning to delete puzzle message',
                [
                    'messageId' => $updateDto->getChatId(),
                    'errorCode' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            );
        }
    }

    /**
     * @param PuzzleAnswerTelegramUpdateDto $updateDto
     */
    private function deleteEnterMessage(PuzzleAnswerTelegramUpdateDto $updateDto): void
    {
        try {
            $this->botClient->deleteMessage($updateDto->getChatId(), $updateDto->getEnterMessageId());
        } catch (BotClientResponseException $exception) {
            $this->logger->warning(
                'Warning to delete enter message',
                [
                    'messageId' => $updateDto->getChatId(),
                    'errorCode' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            );
        }
    }

    /**
     * @param PuzzleTaskDto $puzzleTaskDto
     * @param BotSettingsDto $botSettingsDto
     */
    private function resendPuzzleMessage(PuzzleTaskDto $puzzleTaskDto, BotSettingsDto $botSettingsDto): void
    {
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($botSettingsDto->getPuzzlesSettings());
        $puzzleDto = $puzzleGenerator->generate();
        $this->puzzleTaskService->savePuzzleTask(
            $puzzleTaskDto->getChatId(),
            $puzzleTaskDto->getUserId(),
            $puzzleDto->getAnswer(),
            $puzzleTaskDto->getTaskMessageId(),
            $puzzleTaskDto->getAttempt() + 1
        );
        $question = 'Неверный ответ. Попытайтесь еще раз. ' . $puzzleDto->getQuestion();
        try {
            $this->botClient->sendKeyboardMarkupMessage(
                $puzzleTaskDto->getChatId(),
                $puzzleTaskDto->getTaskMessageId(),
                $question,
                $puzzleDto->getChoices()
            );
        } catch (BotClientResponseException $exception) {
            $this->logger->error(
                'Error to send puzzle',
                [
                    'chatId' => $puzzleTaskDto->getChatId(),
                    'messageId' => $puzzleTaskDto->getTaskMessageId(),
                    'errorCode' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            );
        }
    }

    /**
     * @param PuzzleAnswerTelegramUpdateDto $updateDto
     * @param string $welcomeMessage
     */
    private function sendWelcomeMessage(PuzzleAnswerTelegramUpdateDto $updateDto, string $welcomeMessage): void
    {
        $chatId = $updateDto->getChatId();
        $text = sprintf($welcomeMessage, $updateDto->getUser()->getUserName());
        try {
            $this->botClient->sendChatMessage($chatId, $text);
        } catch (BotClientResponseException $exception) {
            $this->logger->warning(
                'Warning to send welcome message',
                [
                    'chatId' => $chatId,
                    'errorCode' => $exception->getCode(),
                    'error' => $exception->getMessage()
                ]
            );
        }
    }
}
