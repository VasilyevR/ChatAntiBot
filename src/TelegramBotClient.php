<?php
declare(strict_types=1);

namespace App;

use App\Dto\TelegramUpdateDtoInterface;
use App\Exception\BotClientResponseException;
use DateTime;
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
use TgBotApi\BotApiBase\Type\MessageType;

class TelegramBotClient
{
    /**
     * @var BotApi
     */
    private $botApi;

    /**
     * @param BotApi $botApi
     */
    public function __construct(BotApi $botApi)
    {
        $this->botApi = $botApi;
    }

    /**
     * @return string
     * @throws BotClientResponseException
     */
    public function getUserName(): string
    {
        try {
            return $this->botApi->getMe(GetMeMethod::create())->username;
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @param int|null $messageOffset
     * @return TelegramUpdateDtoInterface[]
     * @throws BotClientResponseException
     */
    public function getUpdates(?int $messageOffset): array
    {
        $getUpdatesMethod = new GetUpdatesMethod();
        if ($messageOffset) {
            $getUpdatesMethod->offset = $messageOffset;
        }
        try {
            $updates = $this->botApi->getUpdates($getUpdatesMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }

        return UpdatesProvider::getUpdatesDtos($updates);
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @param string $text
     * @param array $choices
     * @return MessageType
     * @throws BotClientResponseException
     */
    public function sendKeyboardMarkupMessage(int $chatId, int $messageId, string $text, array $choices): MessageType
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
        try {
            return $this->botApi->send($sendMessageMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @param int $chatId
     * @param string $text
     * @throws BotClientResponseException
     */
    public function sendChatMessage(int $chatId, string $text): void
    {
        $sendMessageMethod = SendMessageMethod::create($chatId, $text);
        try {
            $this->botApi->send($sendMessageMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @throws BotClientResponseException
     */
    public function deleteMessage(int $chatId, int $messageId): void
    {
        $sendMessageMethod = DeleteMessageMethod::create($chatId, $messageId);
        try {
            $this->botApi->delete($sendMessageMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws BotClientResponseException
     */
    public function muteUser(int $chatId, int $userId): void
    {
        $restrictMemberMethod = new RestrictChatMemberMethod();
        $restrictMemberMethod->chatId = $chatId;
        $restrictMemberMethod->userId = $userId;
        $restrictMemberMethod->canSendMessages = false;
        $restrictMemberMethod->untilDate = new DateTime('+59 minute');
        try {
            $this->botApi->restrict($restrictMemberMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws BotClientResponseException
     */
    public function unmuteUser(int $chatId, int $userId): void
    {
        $restrictMemberMethod = new RestrictChatMemberMethod();
        $restrictMemberMethod->chatId = $chatId;
        $restrictMemberMethod->userId = $userId;
        $restrictMemberMethod->canSendMessages = true;
        $restrictMemberMethod->untilDate = new DateTime('+60 minute');
        try {
            $this->botApi->restrict($restrictMemberMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @throws BotClientResponseException
     */
    public function banUser(int $chatId, int $userId): void
    {
        $kickChatMemberMethod = KickChatMemberMethod::create($chatId, $userId);
        $kickChatMemberMethod->untilDate = new DateTime('+1 week');
        try {
            $this->botApi->kick($kickChatMemberMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }
}
