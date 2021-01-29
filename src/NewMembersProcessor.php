<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use App\Puzzle\PuzzleFactory;
use SQLite3;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Method\RestrictChatMemberMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UserType;

class NewMembersProcessor
{
    /**
     * @var BotApi
     */
    private $botApi;

    /**
     * @var PuzzleTask
     */
    private $puzzleTaskService;

    /**
     * @var ChatSettings
     */
    private $chatSettingsService;

    /**
     * @param BotApi $botApi
     * @param SQLite3 $database
     */
    public function __construct(BotApi $botApi, SQLite3 $database)
    {
        $this->botApi = $botApi;
        $this->puzzleTaskService = new PuzzleTask($database);
        $this->chatSettingsService = new ChatSettings($database);

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
            $this->sendPuzzle(
                $chatId,
                $message->messageId,
                $puzzleDto->getQuestion(),
                $puzzleDto->getChoices()
            );
            $this->muteUser($chatId, $newChatMemberId);
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
     * @param int $messageId
     * @param string $question
     * @param array $choices
     * @throws ResponseException
     */
    private function sendPuzzle(int $chatId, int $messageId, string $question, array $choices): void
    {
        $keyboardButtons = [];
        foreach ($choices as $choice) {
            $button = new InlineKeyboardButtonType();
            $button->text = $choice;
            $button->callbackData = $choice;
            $keyboardButtons[] = $button;
        }
        $replyMarkUp = new InlineKeyboardMarkupType();
        $replyMarkUp->inlineKeyboard = [$keyboardButtons];
        $sendMessageMethod = SendMessageMethod::create($chatId, $question);
        $sendMessageMethod->replyMarkup = $replyMarkUp;
        $sendMessageMethod->disableNotification = true;
        $sendMessageMethod->replyToMessageId = $messageId;
        $sendMessageMethod->parseMode = 'Markdown';
        $this->botApi->send($sendMessageMethod);
    }

    /**
     * @param int $chatId
     * @param int $timeOut
     * @throws ResponseException
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
        $sendMessageMethod = SendMessageMethod::create($chatId, $text);
        $this->botApi->send($sendMessageMethod);
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    private function muteUser(int $chatId, int $userId): void
    {
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = false;
        $this->botApi->restrict($restrictChatMemberMethod);
    }
}
