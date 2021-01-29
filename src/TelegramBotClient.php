<?php
declare(strict_types=1);

namespace App;

use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\Exception\ResponseException;
use TgBotApi\BotApiBase\Method\DeleteMessageMethod;
use TgBotApi\BotApiBase\Method\GetMeMethod;
use TgBotApi\BotApiBase\Method\GetUpdatesMethod;
use TgBotApi\BotApiBase\Method\KickChatMemberMethod;
use TgBotApi\BotApiBase\Method\RestrictChatMemberMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class TelegramBotClient
{
    /**
     * @var BotApi
     */
    private $botApi;

    public function __construct(BotApi $botApi)
    {
        $this->botApi = $botApi;
    }

    /**
     * @return string
     * @throws ResponseException
     */
    public function getUserName(): string
    {
        return $this->botApi->getMe(GetMeMethod::create())->username;
    }

    /**
     * @param int|null $messageOffset
     * @return UpdateType[]
     * @throws ResponseException
     */
    public function getUpdates(?int $messageOffset): array
    {
        $getUpdates = new GetUpdatesMethod();
        if ($messageOffset) {
            $getUpdates->offset = $messageOffset;
        }
        return $this->botApi->getUpdates($getUpdates);
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @param string $text
     * @param array $choices
     * @throws ResponseException
     */
    public function sendKeyboardMarkupMessage(int $chatId, int $messageId, string $text, array $choices): void
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
        $sendMessageMethod = SendMessageMethod::create($chatId, $text);
        $sendMessageMethod->replyMarkup = $replyMarkUp;
        $sendMessageMethod->disableNotification = true;
        $sendMessageMethod->replyToMessageId = $messageId;
        $sendMessageMethod->parseMode = 'Markdown';
        $this->botApi->send($sendMessageMethod);
    }

    /**
     * @param int $chatId
     * @param string $text
     * @throws ResponseException
     */
    public function sendChatMessage(int $chatId, string $text): void
    {
        $sendMessageMethod = SendMessageMethod::create($chatId, $text);
        $this->botApi->send($sendMessageMethod);
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @throws ResponseException
     */
    public function deleteMessage(int $chatId, int $messageId): void
    {
        $sendMessageMethod = DeleteMessageMethod::create($chatId, $messageId);
        $this->botApi->delete($sendMessageMethod);
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    public function muteUser(int $chatId, int $userId): void
    {
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = false;
        $this->botApi->restrict($restrictChatMemberMethod);
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    public function unmuteUser(int $chatId, int $userId): void
    {
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = true;
        $this->botApi->restrict($restrictChatMemberMethod);
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws ResponseException
     */
    public function banUser(int $chatId, int $userId): void
    {
        $kickChatMemberMethod = KickChatMemberMethod::create($chatId, $userId);
        $this->botApi->kick($kickChatMemberMethod);
    }
}
