<?php
declare(strict_types=1);

namespace App;

use App\Dto\NewMemberTelegramUpdateDto;
use App\Dto\PuzzleAnswerTelegramUpdateDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\Dto\UserDto;
use App\Exception\BotClientResponseException;
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
use TgBotApi\BotApiBase\Type\UserType;

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
        return $this->getUpdateDtos($updates);
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @param string $text
     * @param array $choices
     * @throws BotClientResponseException
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
        try {
            $this->botApi->send($sendMessageMethod);
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
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = false;
        try {
            $this->botApi->restrict($restrictChatMemberMethod);
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
        $restrictChatMemberMethod = new RestrictChatMemberMethod();
        $restrictChatMemberMethod->chatId = $chatId;
        $restrictChatMemberMethod->userId = $userId;
        $restrictChatMemberMethod->canSendMessages = true;
        try {
            $this->botApi->restrict($restrictChatMemberMethod);
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
        try {
            $this->botApi->kick($kickChatMemberMethod);
        } catch (ResponseException $exception) {
            throw new BotClientResponseException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @param UpdateType[] $updates
     * @return TelegramUpdateDtoInterface[]
     */
    private function getUpdateDtos(array $updates): array
    {
        $updateResultsDtos = [];
        foreach ($updates as $update) {
            if (null !== $update->message && !empty($update->message->newChatMembers)) {
                $updateResultsDtos[] = $this->getNewMemberUpdateDto($update);
                continue;
            }
            if (null !== $update->callbackQuery->data && $this->isCorrectPuzzleAnswerUpdate($update)) {
                $updateResultsDtos[] = $this->getPuzzleAnswerUpdateDto($update);
            }
        }

        return $updateResultsDtos;
    }

    /**
     * @param UpdateType $update
     * @return NewMemberTelegramUpdateDto
     */
    private function getNewMemberUpdateDto(UpdateType $update): NewMemberTelegramUpdateDto
    {
        $newMembers = array_map(
            function (UserType $userType) {
                $userName = $userType->username ?? $userType->firstName . ' ' . $userType->lastName;
                return new UserDto($userType->id, $userName);
            },
            $update->message->newChatMembers
        );
        return new NewMemberTelegramUpdateDto(
            $update->updateId,
            $update->message->chat->id,
            $update->message->messageId,
            $newMembers
        );
    }

    /**
     * @param UpdateType $update
     * @return PuzzleAnswerTelegramUpdateDto
     */
    private function getPuzzleAnswerUpdateDto(UpdateType $update): PuzzleAnswerTelegramUpdateDto
    {
        $message = $update->callbackQuery->message;
        $replyToMessage = $update->callbackQuery->message->replyToMessage;
        $userType = $replyToMessage->from;
        $userDto = new UserDto(
            $userType->id,
            $userType->username ?? $userType->firstName . ' ' . $userType->lastName
        );
        return new PuzzleAnswerTelegramUpdateDto(
            $update->updateId,
            $message->chat->id,
            $userDto,
            $replyToMessage->messageId,
            $message->messageId,
            $update->callbackQuery->data);
    }

    /**
     * @param UpdateType $update
     * @return bool
     */
    private function isCorrectPuzzleAnswerUpdate(UpdateType $update): bool
    {
        if (null === $update->callbackQuery
            || null === $update->callbackQuery->data
            || null === $update->callbackQuery->message
        ) {
            return false;
        }
        $message = $update->callbackQuery->message;
        $replyToMessage = $update->callbackQuery->message->replyToMessage;
        if ($replyToMessage->from->id !== $update->callbackQuery->from->id) {
            return false;
        }
        if ($message->chat->id !== $replyToMessage->chat->id) {
            return false;
        }
        return !(null === $replyToMessage || null === $replyToMessage->from);
    }
}
