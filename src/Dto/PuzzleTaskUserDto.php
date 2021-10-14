<?php
declare(strict_types=1);

namespace App\Dto;

class PuzzleTaskUserDto
{
    /**
     * @var int
     */
    private $chatId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $enterMessageId;

    /**
     * @var int
     */
    private $puzzleMessageId;

    /**
     * @param int $chatId
     * @param int $userId
     * @param int $enterMessageId
     * @param int $puzzleMessageId
     */
    public function __construct(int $chatId, int $userId, int $enterMessageId, int $puzzleMessageId) {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->enterMessageId = $enterMessageId;
        $this->puzzleMessageId = $puzzleMessageId;
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
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
}
