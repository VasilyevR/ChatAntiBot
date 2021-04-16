<?php
declare(strict_types=1);

namespace App;

use App\Dto\NewMemberTelegramUpdateDto;
use App\Dto\PuzzleAnswerTelegramUpdateDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\Dto\UnnecessaryTelegramUpdateDto;
use App\Dto\UserDto;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;

class UpdatesProvider
{
    /**
     * @param UpdateType $update
     * @return NewMemberTelegramUpdateDto
     */
    private static function getNewMemberUpdateDto(UpdateType $update): NewMemberTelegramUpdateDto
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
            $update->message->date,
            $update->message->chat->id,
            $update->message->messageId,
            $newMembers
        );
    }

    /**
     * @param UpdateType $update
     * @return PuzzleAnswerTelegramUpdateDto
     */
    private static function getPuzzleAnswerUpdateDto(UpdateType $update): PuzzleAnswerTelegramUpdateDto
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
     * @return UnnecessaryTelegramUpdateDto
     */
    private static function getUnnecessaryUpdateDto(UpdateType $update): UnnecessaryTelegramUpdateDto
    {
        return new UnnecessaryTelegramUpdateDto(
            $update->updateId
        );
    }

    /**
     * @param UpdateType $update
     * @return bool
     */
    private static function isCorrectPuzzleAnswerUpdate(UpdateType $update): bool
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

    /**
     * @param UpdateType $update
     * @return bool
     */
    private static function isCorrectNewMemberUpdate(UpdateType $update): bool
    {
        return null !== $update->message
            && !empty($update->message->newChatMembers
                && !empty($update->message->chat->id));
    }

    /**
     * @param UpdateType[] $updates
     * @return TelegramUpdateDtoInterface[]
     */
    public static function getUpdatesDtos(array $updates): array
    {
        $updateResultsDtos = [];
        foreach ($updates as $update) {
            if (self::isCorrectNewMemberUpdate($update)) {
                $updateResultsDtos[] = self::getNewMemberUpdateDto($update);
                continue;
            }
            if (self::isCorrectPuzzleAnswerUpdate($update)) {
                $updateResultsDtos[] = self::getPuzzleAnswerUpdateDto($update);
                continue;
            }
            $updateResultsDtos[] = self::getUnnecessaryUpdateDto($update);
        }

        return $updateResultsDtos;
    }
}
