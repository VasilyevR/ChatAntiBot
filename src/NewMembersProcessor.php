<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use App\Puzzle\PuzzleFactory;
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
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database)
    {
        $this->botClient = $botClient;
        $this->puzzleTaskService = new PuzzleTask($database);
    }

    /**
     * @param MessageType $message
     * @param BotSettingsDto $botSettingsDto
     * @throws ResponseException
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
            $this->botClient->sendKeyboardMarkupMessage(
                $chatId,
                $message->messageId,
                $puzzleDto->getQuestion(),
                $puzzleDto->getChoices()
            );
            $this->botClient->muteUser($chatId, $newChatMemberId);
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
        $this->botClient->sendChatMessage($chatId, $text);
    }
}
