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
     * @param int $chatId
     * @param int $userId
     */
    public function __construct(int $chatId, int $userId) {
        $this->chatId = $chatId;
        $this->userId = $userId;
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
}
