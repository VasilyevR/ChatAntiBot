<?php
declare(strict_types=1);

namespace App\Dto;

use App\Enum\TelegramUpdateEnum;

class PuzzleAnswerTelegramUpdateDto implements TelegramUpdateDtoInterface
{
    /**
     * @var int
     */
    private $updateId;

    /**
     * @var int
     */
    private $chatId;

    /**
     * @var UserDto
     */
    private $user;

    /**
     * @var int
     */
    private $enterMessageId;

    /**
     * @var int
     */
    private $puzzleMessageId;

    /**
     * @var string
     */
    private $answer;

    /**
     * @param int $updateId
     * @param int $chatId
     * @param UserDto $user
     * @param int $enterMessageId
     * @param int $puzzleMessageId
     * @param string $answer
     */
    public function __construct(
        int $updateId,
        int $chatId,
        UserDto $user,
        int $enterMessageId,
        int $puzzleMessageId,
        string $answer
    ) {
        $this->updateId = $updateId;
        $this->chatId = $chatId;
        $this->user = $user;
        $this->enterMessageId = $enterMessageId;
        $this->puzzleMessageId = $puzzleMessageId;
        $this->answer = $answer;
    }

    /**
     * @return int
     */
    public function getUpdateId(): int
    {
        return $this->updateId;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return TelegramUpdateEnum::PUZZLE_ANSWER;
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @return UserDto
     */
    public function getUser(): UserDto
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getEnterMessageId(): int
    {
        return $this->enterMessageId;
    }

    /**
     * @return int
     */
    public function getPuzzleMessageId(): int
    {
        return $this->puzzleMessageId;
    }

    /**
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }
}
