<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use App\Puzzle\PuzzleFactory;
use Psr\Log\LoggerInterface;
use SQLite3;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UserType;

class NewMembersProcessor
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
     * @param MessageType $message
     * @param BotSettingsDto $botSettingsDto
     */
    public function processMessage(
        MessageType $message,
        BotSettingsDto $botSettingsDto
    ): void {
        $newChatMembers = $this->getNewChatMembers($message);
        if (empty($newChatMembers)) {
            return;
        }
        $chatId = $message->chat->id;
        foreach ($newChatMembers as $newChatMember) {
            if ($newChatMember->username === $botSettingsDto->getBotUserName()) {
                $this->sendInitInformation($chatId, $botSettingsDto->getTimeOutPuzzleReply());
                continue;
            }
            $newChatMemberId = $newChatMember->id;
            $existPuzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($chatId, $newChatMemberId);
            if (null !== $existPuzzleTaskDto) {
                continue;
            }
            $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($botSettingsDto->getPuzzleType());
            $puzzleDto = $puzzleGenerator->generate();
            $this->puzzleTaskService->savePuzzleTask(
                $chatId,
                $newChatMemberId,
                $puzzleDto->getAnswer(),
                $message->messageId
            );
            try {
                $this->botClient->sendKeyboardMarkupMessage(
                    $chatId,
                    $message->messageId,
                    $puzzleDto->getQuestion(),
                    $puzzleDto->getChoices()
                );
            } catch (ResponseException $e) {
                $this->logger->error(
                    'Error to send puzzle',
                    [
                        'chatId' => $chatId,
                        'messageId' => $message->messageId,
                        'errorCode' => $e->getCode(),
                        'error' => $e->getMessage()
                    ]
                );
                continue;
            }
            try {
                $this->botClient->muteUser($chatId, $newChatMemberId);
            } catch (ResponseException $e) {
                $this->logger->error(
                    'Error to mute new member',
                    [
                        'chatId' => $chatId,
                        'userId' => $newChatMemberId,
                        'errorCode' => $e->getCode(),
                        'error' => $e->getMessage()
                    ]
                );
            }
        }
    }

    /**
     * @param MessageType $message
     * @return UserType[]
     */
    private function getNewChatMembers(MessageType $message): array
    {
        if (null === $message) {
            return [];
        }

        return $message->newChatMembers ?? [];
    }

    /**
     * @param int $chatId
     * @param int $timeOut
     */
    private function sendInitInformation(int $chatId, int $timeOut): void
    {
        $information = 'Здравствуйте! Я бот!
Я умею задавать входящим пользователям простые вопросы.
Тот кто не ответил на них правильно в течение %d минут вероятней всего бот.
Либо просто не захотел или не успел ответить.
Он будет удален мной из этого чата, но может быть добавлен любым админом.
Не забудьте меня сделать админом этого чата!';
        $text = sprintf($information, $timeOut);
        try {
            $this->botClient->sendChatMessage($chatId, $text);
        } catch (ResponseException $e) {
            $this->logger->warning(
                'Warning to send init message',
                [
                    'chatId' => $chatId,
                    'errorCode' => $e->getCode(),
                    'error' => $e->getMessage()
                ]
            );
        }
    }
}
